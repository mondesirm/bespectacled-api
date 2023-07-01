<?php

namespace App\Security;

use App\Exception\InvalidConfirmationTokenException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Security\TokenGenerator;
use App\Email\Mailer;

class UserRecoverService
{

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private TokenGenerator $tokenGenerator,
        private Mailer $mailer
    )
    {
    }

    public function recoverPassword(string $email)
    {
        $this->logger->debug('Fetching user Email');

        $user = $this->userRepository->findOneBy(
            ['email' => $email]
        );

        // User was NOT found Email
        if (!$user) {
            $this->logger->debug('User by email not found');
            throw new \Exception('User by email not found');
        }

        // Create confirmation token
        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );
        $this->entityManager->flush();
        
        // Send e-mail here...
        $this->mailer->sendRecoverPasswordEmail($user);
 

        $this->logger->debug('Password Recovery Email Sent');
    }
}