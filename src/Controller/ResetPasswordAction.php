<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;



class ResetPasswordAction
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $userPasswordHasher,
        private JWTTokenManagerInterface $tokenManager
    ) {
    }

    public function __invoke(User $data)
    {
        $this->validator->validate($data);
        $data->setPassword($this->userPasswordHasher->hashPassword($data, $data->getNewPassword()));
        $data->setPasswordChangeDate(time());
        $this->entityManager->flush($data);
        $token = $this->tokenManager->create($data);
        return new JsonResponse(['token' => $token]);
    }
}
