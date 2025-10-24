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

    #[ORM\Column]
    private ?bool $subscription = null;
        /**
     * @var Collection<int, Song>
     */
    #[ORM\ManyToMany(targetEntity: Song::class)]
    #[ORM\JoinTable(name: 'user_repertoire')]
    private Collection $repertoire;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'addedBy')]
    private Collection $songsAdded;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'lastEditBy')]
    private Collection $songsLastEdited;

    /**
     * @var Collection<int, Wedding>
     */
    #[ORM\ManyToMany(targetEntity: Wedding::class, mappedBy: 'parishUsers')]
    private Collection $weddingsAsParish;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $credits = 0;

    public function __construct()
    {
        $this->weddings = new ArrayCollection();
        $this->weddingsAsMariee = new ArrayCollection();
        $this->weddingsAsMusicians = new ArrayCollection();
        $this->repertoire = new ArrayCollection();
        $this->songsAdded = new ArrayCollection();
        $this->songsLastEdited = new ArrayCollection();
        $this->weddingsAsParish = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * @return Collection<int, Song>
     */
    public function getRepertoire(): Collection
    {
        return $this->repertoire;
    }

    public function addSongToRepertoire(Song $song): static
    {
        if (!$this->repertoire->contains($song)) {
            $this->repertoire->add($song);
        }
        return $this;
    }

    public function removeSongFromRepertoire(Song $song): static
    {
        $this->repertoire->removeElement($song);
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
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

    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
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

    public function isSubscription(): ?bool
    {
        return $this->subscription;
    }

    public function setSubscription(bool $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = max(0, $credits);

        return $this;
    }

    public function addCredits(int $amount): static
    {
        if ($amount > 0) {
            $this->credits += $amount;
        }

        return $this;
    }

    public function removeCredits(int $amount): static
    {
        if ($amount > 0) {
            $this->credits = max(0, $this->credits - $amount);
        }

        return $this;
    }

    public function hasCredits(int $amount = 1): bool
    {
        return $this->credits >= $amount;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongsAdded(): Collection
    {
        return $this->songsAdded;
    }

    public function addSongsAdded(Song $songsAdded): static
    {
        if (!$this->songsAdded->contains($songsAdded)) {
            $this->songsAdded->add($songsAdded);
            $songsAdded->setAddedBy($this);
        }

        return $this;
    }

    public function removeSongsAdded(Song $songsAdded): static
    {
        if ($this->songsAdded->removeElement($songsAdded)) {
            // set the owning side to null (unless already changed)
            if ($songsAdded->getAddedBy() === $this) {
                $songsAdded->setAddedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongsLastEdited(): Collection
    {
        return $this->songsLastEdited;
    }

    public function addSongsLastEdited(Song $songsLastEdited): static
    {
        if (!$this->songsLastEdited->contains($songsLastEdited)) {
            $this->songsLastEdited->add($songsLastEdited);
            $songsLastEdited->setLastEditBy($this);
        }

        return $this;
    }

    public function removeSongsLastEdited(Song $songsLastEdited): static
    {
        if ($this->songsLastEdited->removeElement($songsLastEdited)) {
            // set the owning side to null (unless already changed)
            if ($songsLastEdited->getLastEditBy() === $this) {
                $songsLastEdited->setLastEditBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Wedding>
     */
    public function getWeddingsAsParish(): Collection
    {
        return $this->weddingsAsParish;
    }

    public function addWeddingsAsParish(Wedding $weddingsAsParish): static
    {
        if (!$this->weddingsAsParish->contains($weddingsAsParish)) {
            $this->weddingsAsParish->add($weddingsAsParish);
            $weddingsAsParish->addParishUser($this);
        }

        return $this;
    }

    public function removeWeddingsAsParish(Wedding $weddingsAsParish): static
    {
        if ($this->weddingsAsParish->removeElement($weddingsAsParish)) {
            $weddingsAsParish->removeParishUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }
}