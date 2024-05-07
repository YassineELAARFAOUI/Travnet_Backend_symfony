<?php

namespace App\Entity;

use App\Entity\AccBusiness;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ID', columns: ['id'])]
class Chambres implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    #[ORM\Column(type: 'integer')]
    private ?string $numeroPersonne = null;
    
    #[ORM\Column(type: 'integer')]
    private ?int $numeroChambre = null;
    
    #[ORM\Column(type: 'float')]
    private ?float $price = null;
    
    #[ORM\Column(type: 'integer')]
    private ?int $surface = null;
    
    #[ORM\Column(type: 'boolean')]
    private bool $climatisation = false;
    
    #[ORM\Column(type: 'boolean')]
    private bool $salleDebain = false;
    
    
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDepublication = null;
    
    #[ORM\Column(type: 'text')]
    private ?string $description = null;
    
    #[ORM\Column(type: 'string', length: 9, unique: true)]
    private ?string $pattenteDeHotel = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getNumeroPersonne(): ?string
    {
        return $this->numeroPersonne;
    }

    public function setNumeroPersonne(?string $numeroPersonne): self
    {
        $this->numeroPersonne = $numeroPersonne;

        return $this;
    }

    public function getNumeroChambre(): ?int
    {
        return $this->numeroChambre;
    }

    public function setNumeroChambre(?int $numeroChambre): self
    {
        $this->numeroChambre = $numeroChambre;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSurface(): ?int
    {
        return $this->surface;
    }

    public function setSurface(?int $surface): self
    {
        $this->surface = $surface;

        return $this;
    }

    public function getClimatisation(): bool
    {
        return $this->climatisation;
    }

    public function setClimatisation(bool $climatisation): self
    {
        $this->climatisation = $climatisation;

        return $this;
    }

    public function getSalleDeBain(): bool
    {
        return $this->salleDebain;
    }

    public function setSalleDeBain(bool $salleDebain): self
    {
        $this->salleDebain = $salleDebain;

        return $this;
    }

    public function getDateDepublication(): ?\DateTimeInterface
    {
        return $this->dateDepublication;
    }

    public function setDateDepublication(?\DateTimeInterface $dateDepublication): self
    {
        $this->dateDepublication = $dateDepublication;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPattenteDeHotel(): ?string
    {
        return $this->pattenteDeHotel;
    }

    public function setPattenteDeHotel(?string $pattenteDeHotel): self
    {
        $this->pattenteDeHotel = $pattenteDeHotel;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires ou sensibles sur l'utilisateur, effacez-les ici
        // $this->plainPassword = null;
    }

    public function getRoles(): array
    {
        // Comme vous avez supprimé la propriété des rôles, vous pouvez retourner un tableau vide ici
        return [];
    }
}
