<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtistsController extends AbstractController
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke()
    {
        // Issue with PostgreSQL
        return $this->userRepository->findByRole('ROLE_ARTIST');
    }
}