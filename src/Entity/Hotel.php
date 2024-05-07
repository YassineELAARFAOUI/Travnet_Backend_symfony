<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ID', columns: ['id'])]
class Hotel implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 9)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rate = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $city = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'text', length: 255)]
    private ?string $img = null;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    private ?string $idAccBussiness = null;

    // Getters and Setters

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

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

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getIdAccBussiness(): ?string
    {
        return $this->idAccBussiness;
    }

    public function setIdAccBussiness(?string $idAccBussiness): self
    {
        $this->idAccBussiness = $idAccBussiness;

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
