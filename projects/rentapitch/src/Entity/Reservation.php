<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: "reservations")]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['reservation:read'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Pitch::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read'])]
    private Pitch $pitch;

    #[ORM\Column(type: "datetime")]
    #[Groups(['reservation:read'])]
    private \DateTimeInterface $startTime;

    #[ORM\Column(type: "datetime")]
    #[Groups(['reservation:read'])]
    private \DateTimeInterface $endTime;

    #[ORM\Column(type: "datetime")]
    #[Groups(['reservation:read'])]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToOne(targetEntity: Review::class, mappedBy: "reservation", cascade: ["persist", "remove"])]
    private ?Review $review = null;

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

    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): \DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;
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

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): self
    {
        $this->review = $review;

        // Powiązanie w drugą stronę
        if ($review !== null && $review->getReservation() !== $this) {
            $review->setReservation($this);
        }

        return $this;
    }
}
