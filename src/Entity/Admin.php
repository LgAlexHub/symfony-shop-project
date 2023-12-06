<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use App\Entity\Trait\TimestampableWithIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableWithIdTrait;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    #[ORM\Column(type: Types::STRING, nullable: false, unique: true)]
    private ?string $pseudonyme;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, nullable: false, unique: true)]
    private ?string $email;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private ?string $password;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * Get the value of pseudonyme
     */
    public function getPseudonyme()
    {
        return $this->pseudonyme;
    }

    /**
     * Set the value of pseudonyme
     *
     * @return  self
     */
    public function setPseudonyme($pseudonyme)
    {
        $this->pseudonyme = $pseudonyme;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

}
