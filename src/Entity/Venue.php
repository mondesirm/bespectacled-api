<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VenueRepository;
use ApiPlatform\Metadata\ApiResource;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[UniqueEntity(fields: 'name', message: 'This venue already exists.')]
#[ApiResource(
    normalizationContext: ['groups' => ['venue:read']],
    denormalizationContext: ['groups' => ['venue:write']],
    // operations: [
    //     'get' => [
    //         'normalization_context' => ['groups' => ['venue:read']]
    //     ],
    //     'post' => [
    //         'denormalization_context' => ['groups' => ['venue:write']]
    //     ]
    // ],
    // itemOperations: [
    //     'get' => [
    //         'normalization_context' => ['groups' => ['venue:read']]
    //     ],
    //     'put' => [
    //         'denormalization_context' => ['groups' => ['venue:write']]
    //     ],
    //     'delete' => [
    //         'denormalization_context' => ['groups' => ['venue:write']]
    //     ]
    // ]
)]
class Venue
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    #[Groups(['venue:read', 'event:read'])]
    private ?int $id = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(length: 128, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotBlank, Assert\Positive]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?float $price = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?int $seats = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['venue:read', 'venue:write', 'event:read'])]
    private ?string $src = null;

    #[Groups(['venue:read'])]
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: Event::class)]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): self
    {
        $this->src = $src;

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
            $event->setVenue($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getVenue() === $this) {
                $event->setVenue(null);
            }
        }

        return $this;
    }
}
