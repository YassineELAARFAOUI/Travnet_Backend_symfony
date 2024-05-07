<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ID', columns: ['id'])]
class Reclamation implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 50)]
    private ?string $email = null;
    #[ORM\Column(type: 'text')]
    private ?string $description = null;
    #[ORM\Column(type: 'string', length: 9)]
    private ?string $pattenteDeHotel = null;
    #[ORM\Column(type: 'integer')]
    private ?int $numeroChambre = null;
    private ?\DateTimeInterface $dateDereclamation = null;
   
  

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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
    public function getEmail(): ?string{
        return $this->email;
    }
    public function setEmail(string $email): static{
        $this->email = $email;
        return $this;
    }
  

    public function getDateDereclamation(): ?\DateTimeInterface
    {
        return $this->dateDereclamation;
    }

    public function setDateDereclamation(?\DateTimeInterface $dateDereclamation): self
    {
        $this->dateDereclamation = $dateDereclamation;

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
