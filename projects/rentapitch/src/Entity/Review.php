<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "reviews", uniqueConstraints: [
    new ORM\UniqueConstraint(name: "unique_user_pitch_reservation", columns: ["user_id", "pitch_id", "reservation_id"])
])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Pitch::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(nullable: false)]
    private Pitch $pitch;

    #[ORM\Column(type: "smallint")]
    private int $rating;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToOne(targetEntity: Reservation::class, inversedBy: "review")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Reservation $reservation;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPitch(): Pitch
    {
        return $this->pitch;
    }

    public function setPitch(Pitch $pitch): self
    {
        $this->pitch = $pitch;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException("Rating must be between 1 and 5.");
        }
        $this->rating = $rating;
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

    public function getReservation(): Reservation
    {
        return $this->reservation;
    }

    public function setReservation(Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }
}
