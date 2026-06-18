<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(length: 255)]
    private string $photo = 'default.png';

    #[ORM\Column]
    private int $credits = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Vehicule::class)]
    private Collection $vehicules;

    #[ORM\OneToMany(mappedBy: 'conducteur', targetEntity: Trajet::class)]
    private Collection $trajetsConducteur;

    #[ORM\OneToMany(mappedBy: 'passager', targetEntity: Reservation::class)]
    private Collection $reservations;

    

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->trajetsConducteur = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->vehicules = new ArrayCollection();
        // sécurité : rôle par défaut
        $this->roles = ['ROLE_USER'];

       
    }

    // ===================== ID =====================
    public function getId(): ?int
    {
        return $this->id;
    }

    // ===================== IDENTITÉ =====================
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    // ===================== PASSWORD =====================
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    // ===================== ROLES (PROPRE SYMFONY) =====================
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): static
    {
        $this->roles = array_filter(
            $this->roles,
            fn ($r) => $r !== $role
        );

        return $this;
    }

    // ===================== STATUS =====================
    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $active): static
    {
        $this->isActive = $active;
        return $this;
    }

    // ===================== PHOTO =====================
    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    // ===================== CRÉDITS =====================
    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;
        return $this;
    }

    public function addCredits(int $credits): static
    {
        $this->credits += $credits;
        return $this;
    }

    public function removeCredits(int $credits): static
    {
        $this->credits = max(0, $this->credits - $credits);
        return $this;
    }

    // ===================== DATE =====================
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ===================== RELATIONS =====================
    public function getTrajetsConducteur(): Collection
    {
        return $this->trajetsConducteur;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }
    // ===================== _To_String du conducteur Pseudo =====================
    public function __toString(): string
    {
        return $this->pseudo;
    }
}