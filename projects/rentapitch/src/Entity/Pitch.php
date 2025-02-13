<?php

namespace App\Entity;

use App\Repository\PitchRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

enum PitchType: string
{
    case FOOTBALL = 'Piłka nożna';
    case BASKETBALL = 'Koszykówka';
    case TENNIS = 'Tenis';
}

#[ORM\Entity(repositoryClass: PitchRepository::class)]
class Pitch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "string")]
    private string $location;

    #[ORM\Column(type: "string", enumType: PitchType::class, options: ["default" => "FOOTBALL"])]
    private ?PitchType $type = PitchType::FOOTBALL;
    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: false, options: ["default" => 0.00])]
    private float $pricePerHour = 0.00;

    #[ORM\Column(type: "decimal", precision: 10, scale: 6)]
    private float $latitude;

    #[ORM\Column(type: "decimal", precision: 10, scale: 6)]
    private float $longitude;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: "pitch", cascade: ["remove"])]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: "pitch", cascade: ["remove"])]
    private Collection $reviews;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "ownedPitches")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $owner = null;
    public function getType(): ?PitchType
    {
        return $this->type;
    }

    public function setType(PitchType $type): self
    {
        $this->type = $type;

        return $this;
    }
    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function isOwner(?User $user): bool
    {
        return $this->owner !== null && $user !== null && $this->owner->getId() === $user->getId();
    }
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getLatitude(): float
    {
        return (float) $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = (string) $latitude;
        return $this;
    }

    public function getLongitude(): float
    {
        return (float) $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = (string) $longitude;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }
    public function getPricePerHour(): float
    {
        return (float) $this->pricePerHour;
    }

    public function setPricePerHour(float $pricePerHour): self
    {
        $this->pricePerHour = (string) $pricePerHour;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setPitch($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getPitch() === $this) {
                $reservation->setPitch(null);
            }
        }
        return $this;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setPitch($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getPitch() === $this) {
                $review->setPitch(null);
            }
        }
        return $this;
    }
}