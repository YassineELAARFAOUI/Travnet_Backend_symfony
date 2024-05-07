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
    private ?\DateTimeInterface $dateCheck_in = null;
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCheck_out = null;
    #[ORM\Column(type: 'boolean')]
    private bool $confirmation = false;
  

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

    public function getDateCheck_in(): ?\DateTimeInterface
    {
        return $this->dateCheck_in;
    }

    public function setDateCheck_in(?\DateTimeInterface $dateCheck_in): self
    {
        $this->dateCheck_in = $dateCheck_in;

        return $this;
    }
    public function getDateCheck_out(): ?\DateTimeInterface
    {
        return $this->dateCheck_out;
    }

    public function setDateCheck_out(?\DateTimeInterface $dateCheck_out): self
    {
        $this->dateCheck_out = $dateCheck_out;

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
