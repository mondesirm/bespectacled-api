<?php
// src/DataPersister/UserDataPersister.php

namespace App\DataPersister;

use App\Entity\User;
use App\Email\Mailer;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
// use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *
 */
class UserDataPersister /* implements ContextAwareDataPersisterInterface */
{
	public function __construct(
		private Mailer $mailer,
		private ManagerRegistry $doctrine,
		private TokenGenerator $tokenGenerator,
		private UserPasswordHasherInterface $hasher,
		private EntityManagerInterface $entityManager
	) {}

	/**
	 * {@inheritdoc}
	 */
	public function supports($data, array $context = []): bool
	{
		return $data instanceof User;
	}

	/**
	 * @param User $data
	 */
	public function persist($data, array $context = [])
	{
		$data->setEnabled(true);
		$data->setRoles(['ROLE_ADMIN']);

		if ($data->getPlainPassword()) {
			$data->setPassword($this->hasher->hashPassword($data, $data->getPlainPassword()));

			$data->eraseCredentials();
			$data->setConfirmationToken($this->tokenGenerator->getRandomSecureToken());

			// Send e-mail here...
			$this->mailer->sendConfirmationEmail($data);
		}

		// TODO inject UserRepository instead of using the registry
		$amountOfUsers = $this->doctrine->getRepository(User::class)->countall();
		if ($amountOfUsers == 0) $data->setRoles(['ROLE_ADMIN']);

		$this->entityManager->persist($data);
		$this->entityManager->flush();
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($data, array $context = [])
	{
		$this->entityManager->remove($data);
		$this->entityManager->flush();
	}
}