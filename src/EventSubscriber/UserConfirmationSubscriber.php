<?php

namespace App\EventSubscriber;

use App\Entity\UserConfirmation;
use App\Security\UserConfirmationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
// use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class UserConfirmationSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserConfirmationService
     */

    public function __construct(
        private UserConfirmationService $userConfirmationService
    ) {
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'confirmUser',
                EventPriorities::POST_VALIDATE,
            ],
        ];
    }

  //  public function confirmUser(GetResponseForControllerResultEvent $event)
    public function confirmUser(ViewEvent $event)
    {
        $request = $event->getRequest();

        if ('api_user_confirmations_post_collection' !==
            $request->get('_route')) {
            return;
        }

        /** @var UserConfirmation $confirmationToken */
        $confirmationToken = $event->getControllerResult();

        $this->userConfirmationService->confirmUser(
            $confirmationToken->confirmationToken
        );

        $event->setResponse(new JsonResponse(null, Response::HTTP_OK));
    }
}