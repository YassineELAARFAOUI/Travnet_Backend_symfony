<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ID', columns: ['id'])]
class Reservation implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'integer')]
    private ?int $clientId = null;
    #[ORM\Column(type: 'string', length: 9)]
    private ?string $pattenteDeHotel = null;
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $datecheckin = null;
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $datecheckout = null;
    #[ORM\Column(type: 'boolean')]
    private bool $confirmation = false;
    #[ORM\Column(type: 'integer')]
    private ?int $numeroDeChambre = null;
    
  

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

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getConfirmation(): bool
    {
        return $this->confirmation;
    }

    public function setConfirmation(bool $confirmation): self
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function getDatecheckin(): ?\DateTimeInterface
    {
        return $this->datecheckin;
    }

    public function setDateCheckin(?\DateTimeInterface $datecheckin): self
    {
        $this->datecheckin = $datecheckin;

        return $this;
    }
    public function getDatecheckout(): ?\DateTimeInterface
    {
        return $this->datecheckout;
    }

    public function setDatecheckout(?\DateTimeInterface $datecheckout): self
    {
        $this->datecheckout = $datecheckout;

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

    public function getNumeroDeChambre(): ?int
    {
        return $this->numeroDeChambre;
    }

    public function setNumeroDeChambre(?int $numeroDeChambre): self
    {
        $this->numeroDeChambre = $numeroDeChambre;

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