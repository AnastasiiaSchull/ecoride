<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'trajetsConducteur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $conducteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Vehicule $vehicule = null;

    #[ORM\Column(length: 100)]
    private ?string $villeDepart = null;

    #[ORM\Column(length: 100)]
    private ?string $villeArrivee = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateArrivee = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $placesDispo = null;

    #[ORM\Column]
    private bool $eco = false;

    #[ORM\Column(length: 20)]
    private string $statut = 'a_venir';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaires = null;

    // ✅ AJOUT IMPORTANT : preferences
    #[ORM\ManyToMany(targetEntity: Preference::class)]
    private Collection $preferences;

    public function __construct()
    {
        $this->preferences = new ArrayCollection();
    }

    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(Preference $preference): self
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences->add($preference);
        }

        return $this;
    }

    public function removePreference(Preference $preference): self
    {
        $this->preferences->removeElement($preference);
        return $this;
    }

    // ---------------- ID ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    // ---------------- CONDUCTEUR ----------------

    public function getConducteur(): ?User
    {
        return $this->conducteur;
    }

    public function setConducteur(?User $conducteur): static
    {
        $this->conducteur = $conducteur;
        return $this;
    }

    // ---------------- VEHICULE ----------------

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(?Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;
        return $this;
    }

    // ---------------- VILLES ----------------

    public function getVilleDepart(): ?string
    {
        return $this->villeDepart;
    }

    public function setVilleDepart(string $villeDepart): static
    {
        $this->villeDepart = $villeDepart;
        return $this;
    }

    public function getVilleArrivee(): ?string
    {
        return $this->villeArrivee;
    }

    public function setVilleArrivee(string $villeArrivee): static
    {
        $this->villeArrivee = $villeArrivee;
        return $this;
    }

    // ---------------- DATES ----------------

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;
        return $this;
    }

    // ---------------- PRIX ----------------

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    // ---------------- PLACES ----------------

    public function getPlacesDispo(): ?int
    {
        return $this->placesDispo;
    }

    public function setPlacesDispo(int $placesDispo): static
    {
        $this->placesDispo = $placesDispo;
        return $this;
    }

    // ---------------- ECO ----------------

    public function isEco(): bool
    {
        return $this->eco;
    }

    public function setEco(bool $eco): static
    {
        $this->eco = $eco;
        return $this;
    }

    // ---------------- STATUT ----------------

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    // ---------------- COMMENTAIRES ----------------

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): static
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    // ---------------- STRING ----------------

    public function __toString(): string
    {
        return sprintf(
            '%s : %s → %s (%s)',
            $this->conducteur,
            $this->villeDepart,
            $this->villeArrivee,
            $this->dateDepart?->format('d/m/Y H:i')
        );
    }

}