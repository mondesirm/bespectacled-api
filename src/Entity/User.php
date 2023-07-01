<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Controller\ArtistsController;
use App\Controller\ProfileController;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ResetPasswordAction;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
// use Symfony\Component\Security\Core\Authorization\ExpressionLanguage

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user or is_granted('ROLE_ADMIN')",
    operations: [
        new GetCollection(),
        new GetCollection(
            name: 'profile',
            uriTemplate: '/profile',
            controller: ProfileController::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new GetCollection(
            name: 'artists',
            uriTemplate: '/artists',
            controller: ArtistsController::class,
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Get(
            security: "is_granted('PUBLIC_ACCESS')",
            normalizationContext: ['groups' => ['artist:read']]
        ),
        new Post(),
        new Post(
            name: 'register',
            uriTemplate: '/register',
            security: "is_granted('PUBLIC_ACCESS')",
            denormalizationContext: ['groups' => ['user:register']]
        ),
        new Put(),
        new Delete(),
        // "put-password" => [
        //     "method" => "PUT",
        //     "path" => "/users/{id}/reset-password",
        //     "controller" => ResetPasswordAction::class,
        //     "denormalization_context" => ["groups" => ["user:reset:password"]],
        //     "access_control" => "is_granted('IS_AUTHENTICATED_FULLY') and object == user"
        // ]
        // new Put(
        //     uriTemplate: '/users/{id}/reset-password',
        //     controller: ResetPasswordAction::class,
        //     denormalizationContext: ['groups' => ['user:reset:password']],
        //     security: "is_granted('IS_AUTHENTICATED_FULLY') and object == user"
        // )
    ]
)]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'username' => 'ASC'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "`user`")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email', message: 'This email is already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\PrePersist, ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        if ($this->getCreatedAt() === null) $this->setCreatedAt(new \DateTimeImmutable());
        $this->setUpdatedAt(new \DateTimeImmutable());
    }

    #[Groups(['user:read', 'artist:read', 'event:read', 'ticket:read', 'booking:read', 'transaction:read'])]
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:read', 'artist:read', 'user:write', 'user:register', 'event:read', 'ticket:read', 'booking:read', 'transaction:read'])]
    private ?string $username = null;

    #[Assert\Email, Assert\NotBlank]
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'user:register', 'event:read', 'ticket:read', 'booking:read', 'transaction:read'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups('user:read')]
    // Postgres: #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:write', 'user:register'])]
    #[Assert\NotCompromisedPassword()]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}/",
        message: "Password must be eight characters long and contain at least one digit, one upper case letter and one lower case letter"
    )]
    private ?string $password = null;

    // #[Assert\NotBlank]
    #[Groups(['user:write', 'user:register'])]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => '0'])]
    private bool $enabled = false;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_change_date', type: 'integer', nullable: true)]
    private $passwordChangeDate;

    // TODO this class below doesn't exist
    // #[SecurityAssert\UserPassword(message: "Wrong value for your current password")]
    #[Groups('user:reset:password')]
    private $oldPassword;

    #[Groups('user:reset:password')]
    #[Assert\NotBlank(groups: ["user:reset:password"])]
    #[Assert\Regex(
        pattern: "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
        message: "Password must be seven characters long and contain at least one digit, one upper case letter and one lower case letter"
    )]
    private $newPassword;

    #[Groups('user:reset:password')]
    #[Assert\NotBlank(groups: ['user:reset:password'])]
    #[Assert\Expression(
        'this.getNewPassword() === this.getNewRetypedPassword()',
        message: 'passwords does not match'
    )]
    private $newRetypedPassword;

    #[Groups(['user:read', 'artist:read'])]
    #[ORM\ManyToMany(mappedBy: 'artists', targetEntity: Event::class)]
    private Collection $events;

    #[Groups('user:read')]
    #[ORM\OneToMany(mappedBy: 'buyer', targetEntity: Ticket::class)]
    private Collection $tickets;

    #[Groups('user:read')]
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Booking::class)]
    private Collection $bookings;

    #[Groups('user:read')]
    #[ORM\OneToMany(mappedBy: 'buyer', targetEntity: Transaction::class)]
    private Collection $transactions;

    #[ORM\Column]
    #[Groups('user:read')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups('user:read')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups('user:read')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        // https://api-platform.com/docs/core/serialization/#collection-relation
        $this->events = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate($passwordChangeDate)
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewRetypedPassword()
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword($newRetypedPassword)
    {
        $this->newRetypedPassword = $newRetypedPassword;

        return $this;
    }

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addArtist($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removeArtist($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setBuyer($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getBuyer() === $this) {
                $ticket->setBuyer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setClient($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getClient() === $this) {
                $booking->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setBuyer($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getBuyer() === $this) {
                $transaction->setBuyer(null);
            }
        }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}