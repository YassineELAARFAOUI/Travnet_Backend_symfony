<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ID', columns: ['id'])]
class AccClient implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $password = null;


    #[ORM\Column(type: 'boolean')]
    private bool $block = false;


    public function getId(): ?int{
        return $this->id;
    }
    public function setId(int $id): static{
        $this->id = $id;
        return $this;
    }

    public function getFullName(): ?string{
        return $this->fullName;
    }
    public function setFullName(string $fullName): static{
        $this->fullName = $fullName;
        return $this;
    }

    public function getEmail(): ?string{
        return $this->email;
    }
    public function setEmail(string $email): static{
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string{
        return $this->phone;
    }
    public function setPhone(string $phone): static{
        $this->phone = $phone;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $password): static{
        $this->password = $password;
        return $this;
    }

    public function getBlock(): bool{
        return $this->block;
    }
    public function setBlock(bool $block): static{
        $this->block = $block;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // Since you've removed the roles property, you can return an empty array here
        return [];
    }
}
