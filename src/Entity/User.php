<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\OneToMany(mappedBy: 'marie', targetEntity: Wedding::class)]
    private Collection $weddings;

    #[ORM\OneToMany(mappedBy: 'mariee', targetEntity: Wedding::class)]
    private Collection $weddingsAsMariee;

    // src/Entity/User.php
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressPostalCodeAndCity = null;

    /**
     * @var Collection<int, Wedding>
     */
    #[ORM\ManyToMany(targetEntity: Wedding::class, mappedBy: 'musicians')]
    private Collection $weddingsAsMusicians;

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function __construct()
    {
        $this->weddings = new ArrayCollection();
        $this->weddingsAsMariee = new ArrayCollection();
        $this->weddingsAsMusicians = new ArrayCollection();
    }

    // === Mariés (hommes) ===
    public function getWeddings(): Collection
    {
        return $this->weddings;
    }

    public function addWedding(Wedding $wedding): static
    {
        if (!$this->weddings->contains($wedding)) {
            $this->weddings->add($wedding);
            $wedding->setMarie($this);
        }
        return $this;
    }

    public function removeWedding(Wedding $wedding): static
    {
        if ($this->weddings->removeElement($wedding)) {
            if ($wedding->getMarie() === $this) {
                $wedding->setMarie(null);
            }
        }
        return $this;
    }

    // === Mariées (femmes) ===
    public function getWeddingsAsMariee(): Collection
    {
        return $this->weddingsAsMariee;
    }

    public function addWeddingAsMariee(Wedding $wedding): static
    {
        if (!$this->weddingsAsMariee->contains($wedding)) {
            $this->weddingsAsMariee->add($wedding);
            $wedding->setMariee($this);
        }
        return $this;
    }

    public function removeWeddingAsMariee(Wedding $wedding): static
    {
        if ($this->weddingsAsMariee->removeElement($wedding)) {
            if ($wedding->getMariee() === $this) {
                $wedding->setMariee(null);
            }
        }
        return $this;
    }

    // === Méthode pratique pour récupérer tous les mariages ===
    /**
     * Retourne tous les mariages où l'utilisateur est mari ou mariée
     *
     * @return Collection<int, Wedding>
     */
    public function getAllWeddings(): Collection
    {
        return new ArrayCollection(
            array_merge(
                $this->weddings->toArray(),
                $this->weddingsAsMariee->toArray()
            )
        );
    }

    // === Infos utilisateur ===
    public function getId(): ?int
    {
        return $this->id;
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
        return (string) $this->email;
    }

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

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->name;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getAddressPostalCodeAndCity(): ?string
    {
        return $this->addressPostalCodeAndCity;
    }

    public function setAddressPostalCodeAndCity(?string $addressPostalCodeAndCity): static
    {
        $this->addressPostalCodeAndCity = $addressPostalCodeAndCity;

        return $this;
    }

    /**
     * @return Collection<int, Wedding>
     */
    public function getWeddingsAsMusicians(): Collection
    {
        return $this->weddingsAsMusicians;
    }

    public function addWeddingsAsMusician(Wedding $weddingsAsMusician): static
    {
        if (!$this->weddingsAsMusicians->contains($weddingsAsMusician)) {
            $this->weddingsAsMusicians->add($weddingsAsMusician);
            $weddingsAsMusician->addMusician($this);
        }

        return $this;
    }

    public function removeWeddingsAsMusician(Wedding $weddingsAsMusician): static
    {
        if ($this->weddingsAsMusicians->removeElement($weddingsAsMusician)) {
            $weddingsAsMusician->removeMusician($this);
        }

        return $this;
    }
}