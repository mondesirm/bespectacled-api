<?php

namespace App\Controller;

use App\Entity\Transaction;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentController extends AbstractController
{

  #[Route('/payment', name: 'payment')]
  public function index(Request $request, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
  {

    if (!$this->getUser()) {
      return $this->redirectToRoute('cancel_url');
    }
    $error = null;
    $success = null;
    if ($request->query->has('error')) {
      $error = $request->query->get('error');
      if ($error == 'payment_intent_canceled') {
        $error = 'Your payment has been canceled';
      }
      $this->addFlash('error', $error);
    }

    $request = $requestStack->getCurrentRequest();
    $content = $request->getContent();
    $data = json_decode($content, true);
    $amount = $data['amount'];

    if ($amount < 10 || $amount > 10000) {
      $this->addFlash('error', 'The amount has to be between 10€ and 10 000€');
      return $this->redirectToRoute('payment');
    }
    return $this->redirect($this->generateUrl('checkout', ['amount' => $amount], UrlGeneratorInterface::ABSOLUTE_URL));
  }


  #[Route('/checkout', name: 'checkout')]
  public function checkout($stripeSK, Request $request, EntityManagerInterface $entityManager): Response
  {
    Stripe::setApiKey($stripeSK);

    $amount = filter_var($request->request->get('amount'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if (!$amount) {
      $amount = filter_var($request->query->get('amount'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    $amount = number_format((float)$amount, 2, '.', '');
    if ($amount < 10.0 || $amount > 10000.0 || !$amount || (!is_float($amount) && !is_int($amount) && !is_numeric($amount))) {
      return $this->redirect($this->generateUrl('payment', ['error' => 'Minimum amount is 10€'], UrlGeneratorInterface::ABSOLUTE_URL));
    }
    $session = Session::create([
      'payment_method_types' => ['card'],
      'line_items' => [
        [
          'price_data' => [
            'currency' => 'eur',
            'product_data' => [
              'name' => 'Add funds to your League Bet account',
            ],
            'ticket' => 'ticket',
          ],
          'quantity' => 1,
        ]
      ],
      'mode' => 'payment',
      'success_url' => $this->successTransaction($entityManager, $amount),
      'cancel_url' => $this->failedTransaction($entityManager, $amount),
    ]);
    return $this->redirect($session->url, 303);
  }

  public function successTransaction($entityManager, $amount)
  {
    $user = $this->getUser();
    $transaction = new Transaction();
    $transaction->setAmount($amount);
    $transaction->setBuyer($user);
    $transaction->setStatus('success');
    $transaction->setCreatedAt(new \DateTime());

    $user->addFunds($amount);

    $entityManager->persist($user);
    $entityManager->persist($transaction);
    $entityManager->flush();

    return $this->generateUrl('success_url', [], UrlGeneratorInterface::ABSOLUTE_URL);
  }

  public function failedTransaction($entityManager, $amount)
  {
    $user = $this->getUser();
    $transaction = new Transaction();
    $transaction->setAmount($amount);
    $transaction->setBuyer($user);
    $transaction->setStatus('cancelled');
    $transaction->setCreatedAt(new \DateTime());

    $entityManager->persist($transaction);
    $entityManager->flush();

    return $this->generateUrl('cancel_url', [], UrlGeneratorInterface::ABSOLUTE_URL);
  }

  #[Route('/success-url', name: 'success_url')]
  public function successUrl(): Response
  {
    return $this->redirect($this->generateUrl('payment', ['success' => 'payment_intent_succeeded'], UrlGeneratorInterface::ABSOLUTE_URL));
  }


  #[Route('/cancel-url', name: 'cancel_url')]
  public function cancelUrl(): Response
  {
    return $this->redirect($this->generateUrl('payment', ['error' => 'payment_intent_canceled'], UrlGeneratorInterface::ABSOLUTE_URL));
  }
}
