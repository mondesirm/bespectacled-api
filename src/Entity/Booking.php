<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use App\Enum\BookingStatusEnum;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']],
    // security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user or is_granted('ROLE_ADMIN')",
)]
class Booking
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['booking:read', 'booking:write', 'user:read', 'venue:read', 'transaction:read'])]
    private int $status = BookingStatusEnum::PENDING;

    #[Groups(['booking:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups('booking:read')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Assert\NotBlank]
    #[Groups(['booking:read', 'booking:write'])]
    #[ORM\ManyToOne(inversedBy: 'bookings'), ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[Assert\NotBlank]
    #[Groups(['booking:read', 'booking:write'])]
    #[ORM\ManyToOne(inversedBy: 'bookings'), ORM\JoinColumn(nullable: false)]
    private ?Venue $venue = null;

    #[Assert\NotBlank]
    #[Groups(['booking:read', 'booking:write'])]
    #[ORM\ManyToOne(inversedBy: 'bookings'), ORM\JoinColumn(nullable: true)]
    private ?Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(BookingStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

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