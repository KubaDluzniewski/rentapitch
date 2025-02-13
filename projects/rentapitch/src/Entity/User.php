<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 100)]
    private string $name;

    #[ORM\Column(type: "string", length: 100)]
    private string $surname;

    #[ORM\Column(type: "json")]
    private array $roles = [];



    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $reservations;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: "user", cascade: ["remove"])]
    private Collection $reviews;

    #[ORM\OneToMany(targetEntity: Pitch::class, mappedBy: "owner")]
    private Collection $ownedPitches;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }
    public function getOwnedPitches(): Collection
    {
        return $this->ownedPitches;
    }

    public function addOwnedPitch(Pitch $pitch): self
    {
        if (!$this->ownedPitches->contains($pitch)) {
            $this->ownedPitches[] = $pitch;
            $pitch->setOwner($this);
        }
        return $this;
    }

    public function removeOwnedPitch(Pitch $pitch): self
    {
        if ($this->ownedPitches->removeElement($pitch)) {
            if ($pitch->getOwner() === $this) {
                $pitch->setOwner(null);
            }
        }
        return $this;
    }

    public function __construct()
    {
        $this->ownedPitches = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->name;
    }

    public function setFirstName(string $firstName): self
    {
        $this->name = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->surname;
    }

    public function setLastName(string $lastName): self
    {
        $this->surname = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
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

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
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
            $reservation->setUser($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->contains($reservation)) {
            $this->reservations->removeElement($reservation);
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
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
            $review->setUser($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }
        return $this;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }
}