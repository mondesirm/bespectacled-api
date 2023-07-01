<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\EventRepository;
use ApiPlatform\Metadata\ApiResource;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[UniqueEntity(fields: 'title', message: 'This event already exists.')]
#[ApiResource(
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']]
)]
#[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'title' => 'ASC', 'type' => 'ASC', 'price' => 'ASC', 'venue.name' => 'ASC'])]
class Event
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    #[Groups(['event:read', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?int $id = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(length: 128, unique: true)]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?string $type = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    #[ORM\Column(type: 'float')]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?int $price = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?string $src = null;

    #[ORM\JoinColumn]
    #[Assert\NotBlank]
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read', 'schedule:read'])]
    private ?Venue $venue = null;

    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    #[ORM\JoinTable(name: 'event_artist')]
    #[Groups(['event:read', 'event:write', 'venue:read', 'schedule:read'])]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $artists;

    #[Groups(['event:read', 'event:write', 'venue:read', 'user:read', 'artist:read'])]
    #[ORM\OneToMany(targetEntity: Schedule::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $schedules;

    // #[Groups(['event:read', 'event:write'])]
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Ticket::class)]
    private Collection $tickets;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->tickets = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): self
    {
        $this->src = $src;

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

    /**
     * @return Collection<int, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(User $artist): self
    {
        if (!$this->artists->contains($artist)) {
            $this->artists->add($artist);
        }

        return $this;
    }

    public function removeArtist(User $artist): self
    {
        $this->artists->removeElement($artist);

        return $this;
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setEvent($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getEvent() === $this) {
                $schedule->setEvent(null);
            }
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
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                $ticket->setEvent(null);
            }
        }

        return $this;
    }
}
