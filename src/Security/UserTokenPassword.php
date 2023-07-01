<?php

namespace App\Security;

use App\Exception\InvalidConfirmationTokenException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTokenPassword
{

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {}

    public function setTokenAsPassword(string $confirmationToken)
    {
        $this->logger->debug('Fetching user by confirmation token');

        $user = $this->userRepository->findOneBy(
            ['confirmationToken' => $confirmationToken]
        );

        // User was NOT found by confirmation token
        if (!$user) {
            $this->logger->debug('User by confirmation token not found');
            throw new InvalidConfirmationTokenException();
        }

        // It is an User, we need to hash password here
        $user->setPassword(
                $this->userPasswordHasher->hashPassword($user, $confirmationToken)
            );    
        $user->setConfirmationToken(null);
        $this->entityManager->flush();

        $this->logger->debug('New password set');
    }
}