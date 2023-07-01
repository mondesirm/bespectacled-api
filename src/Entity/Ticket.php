<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use App\Enum\TicketStatusEnum;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\UserController;
use App\Repository\TicketRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ResetPasswordAction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: "`ticket`")]
#[ApiResource(
    normalizationContext: ['groups' => ['ticket:read']],
    denormalizationContext: ['groups' => ['ticket:write']],
    // security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user or is_granted('ROLE_ADMIN')",
)]
class Ticket
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['ticket:read', 'ticket:write', 'user:read', 'event:read', 'transaction:read'])]
    private ?string $reference = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['ticket:read', 'ticket:write', 'user:read', 'event:read', 'transaction:read'])]
    private int $status = TicketStatusEnum::PENDING;

    #[Assert\NotBlank]
    #[Groups(['ticket:read', 'ticket:write'])]
    #[ORM\ManyToOne(inversedBy: 'tickets'), ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    #[Assert\NotBlank]
    #[Groups(['ticket:read', 'ticket:write'])]
    #[ORM\ManyToOne(inversedBy: 'tickets'), ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[Assert\NotBlank]
    #[Groups(['ticket:read'])]
    #[ORM\ManyToOne(inversedBy: 'tickets'), ORM\JoinColumn(nullable: false)]
    private ?Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(TicketStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

}
