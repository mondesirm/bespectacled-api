<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\InvalidConfirmationTokenException;

class UserConfirmationService
{
	public function __construct(private LoggerInterface $logger, private UserRepository $userRepository, private EntityManagerInterface $entityManager) {}

	public function confirmUser(string $confirmationToken)
	{
		$this->logger->debug('Fetching user by confirmation token');
		$user = $this->userRepository->findOneBy(compact('confirmationToken'));

		// User was NOT found by confirmation token
		if (!$user) {
			$this->logger->debug('User by confirmation token not found');
			throw new InvalidConfirmationTokenException();
		}

		$user->setEnabled(true);
		$user->setConfirmationToken(null);
		$this->entityManager->flush();

		$this->logger->debug('Confirmed user by confirmation token');
	}
}