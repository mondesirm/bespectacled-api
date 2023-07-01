<?php

namespace App\Entity;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

// #[ApiResource(
//     collectionOperations: [
//         "post" => [
//             "path" => "/users/confirm",
//         ],
//         "get" => []
//     ],
//     itemOperations : []
// )]
class Confirmation
{
    #[Assert\NotBlank]
    #[Assert\Length( min : 30 , max:30)]
    public $confirmationToken;
}