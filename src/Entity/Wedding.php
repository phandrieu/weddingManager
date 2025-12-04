<?php

namespace App\Entity;

use App\Repository\WeddingRepository;
use App\Entity\Song;
use App\Entity\SongType;
use App\Entity\WeddingSongSelection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeddingRepository::class)]
class Wedding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'weddings')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $marie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieFirstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $marieTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieAddressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieAddressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieAddressPostalCodeAndCity = null;

    #[ORM\ManyToOne(inversedBy: 'weddingsAsMariee')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $mariee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeFirstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $marieeTelephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeAddressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeAddressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marieeAddressPostalCodeAndCity = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\ManyToMany(targetEntity: Song::class, inversedBy: 'weddings')]
    private Collection $songs;

    #[ORM\OneToMany(mappedBy: 'wedding', targetEntity: WeddingSongSelection::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $songSelections;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $church = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parish = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressPostalCodeAndCity = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'weddingsAsMusicians')]
    #[ORM\JoinTable(name: 'wedding_musicians')]
    private Collection $musicians;

    /**
     * @var Collection<int, Invitation>
     */
    #[ORM\OneToMany(targetEntity: Invitation::class, mappedBy: 'wedding', orphanRemoval: true, cascade: ['remove'])]
    private Collection $invitations;

    #[ORM\Column(options: ['default' => false])]
    private bool $archive = false;

    #[ORM\Column]
    private ?float $montantTotal = null;

    #[ORM\Column]
    private ?float $montantPaye = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $messe = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priestFirstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priestLastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $priestPhoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priestEMail = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $time = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'weddingsAsParish')]
    #[ORM\JoinTable(name: 'wedding_parish_users')]
    private Collection $parishUsers;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'wedding', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $createdWithCredit = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $requiresCouplePayment = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPaid = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $paymentOption = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesMusiciens = null;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->musicians = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->parishUsers = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->songSelections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarie(): ?User
    {
        return $this->marie;
    }

    public function setMarie(?User $marie): static
    {
        $this->marie = $marie;

        return $this;
    }

    public function getMariee(): ?User
    {
        return $this->mariee;
    }

    public function setMariee(?User $mariee): static
    {
        $this->mariee = $mariee;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->addWedding($this);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            $song->removeWedding($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, WeddingSongSelection>
     */
    public function getSongSelections(): Collection
    {
        return $this->songSelections;
    }

    public function addSongSelection(WeddingSongSelection $selection): static
    {
        if (!$this->songSelections->contains($selection)) {
            $this->songSelections->add($selection);
            $selection->setWedding($this);
        }

        return $this;
    }

    public function removeSongSelection(WeddingSongSelection $selection): static
    {
        if ($this->songSelections->removeElement($selection)) {
            if ($selection->getWedding() === $this) {
                $selection->setWedding(null);
            }
        }

        return $this;
    }

    public function getSongSelectionForType(?SongType $songType): ?WeddingSongSelection
    {
        if (!$songType) {
            return null;
        }

        foreach ($this->songSelections as $selection) {
            if ($selection->getSongType()?->getId() === $songType->getId()) {
                return $selection;
            }
        }

        return null;
    }

    public function getChurch(): ?string
    {
        return $this->church;
    }

    public function setChurch(?string $church): static
    {
        $this->church = $church;

        return $this;
    }

    public function getParish(): ?string
    {
        return $this->parish;
    }

    public function setParish(?string $parish): static
    {
        $this->parish = $parish;

        return $this;
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
     * @return Collection<int, User>
     */
    public function getMusicians(): Collection
    {
        return $this->musicians;
    }

    public function addMusician(User $musician): static
    {
        if (!$this->musicians->contains($musician)) {
            $this->musicians->add($musician);
        }

        return $this;
    }

    public function removeMusician(User $musician): static
    {
        $this->musicians->removeElement($musician);

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): static
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations->add($invitation);
            $invitation->setWedding($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): static
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getWedding() === $this) {
                $invitation->setWedding(null);
            }
        }

        return $this;
    }

    public function isArchive(): bool
    {
        return $this->archive;
    }

    public function setArchive(bool $archive): static
    {
        $this->archive = $archive;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(float $montantTotal): static
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    public function getMontantPaye(): ?float
    {
        return $this->montantPaye;
    }

    public function setMontantPaye(float $montantPaye): static
    {
        $this->montantPaye = $montantPaye;

        return $this;
    }

    public function isMesse(): bool
    {
        return $this->messe;
    }

    public function setMesse(bool $messe): static
    {
        $this->messe = $messe;

        return $this;
    }

    public function getPriestFirstName(): ?string
    {
        return $this->priestFirstName;
    }

    public function setPriestFirstName(?string $priestFirstName): static
    {
        $this->priestFirstName = $priestFirstName;

        return $this;
    }

    public function getPriestLastName(): ?string
    {
        return $this->priestLastName;
    }

    public function setPriestLastName(?string $priestLastName): static
    {
        $this->priestLastName = $priestLastName;

        return $this;
    }

    public function getPriestPhoneNumber(): ?string
    {
        return $this->priestPhoneNumber;
    }

    public function setPriestPhoneNumber(?string $priestPhoneNumber): static
    {
        $this->priestPhoneNumber = $priestPhoneNumber;

        return $this;
    }

    public function getPriestEMail(): ?string
    {
        return $this->priestEMail;
    }

    public function setPriestEMail(?string $priestEMail): static
    {
        $this->priestEMail = $priestEMail;

        return $this;
    }

    public function getTime(): ?\DateTime
    {
        return $this->time;
    }

    public function setTime(?\DateTime $time): static
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParishUsers(): Collection
    {
        return $this->parishUsers;
    }

    public function addParishUser(User $parishUser): static
    {
        if (!$this->parishUsers->contains($parishUser)) {
            $this->parishUsers->add($parishUser);
        }

        return $this;
    }

    public function removeParishUser(User $parishUser): static
    {
        $this->parishUsers->removeElement($parishUser);

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
            $comment->setWedding($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getWedding() === $this) {
                $comment->setWedding(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isCreatedWithCredit(): bool
    {
        return $this->createdWithCredit;
    }

    public function setCreatedWithCredit(bool $createdWithCredit): static
    {
        $this->createdWithCredit = $createdWithCredit;

        return $this;
    }

    public function isRequiresCouplePayment(): bool
    {
        return $this->requiresCouplePayment;
    }

    public function setRequiresCouplePayment(bool $requiresCouplePayment): static
    {
        $this->requiresCouplePayment = $requiresCouplePayment;

        return $this;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getPaymentOption(): ?string
    {
        return $this->paymentOption;
    }

    public function setPaymentOption(?string $paymentOption): static
    {
        $this->paymentOption = $paymentOption;

        return $this;
    }

    public function getMarieFirstName(): ?string
    {
        return $this->marieFirstName;
    }

    public function setMarieFirstName(?string $marieFirstName): static
    {
        $this->marieFirstName = $marieFirstName;

        return $this;
    }

    public function getMarieName(): ?string
    {
        return $this->marieName;
    }

    public function setMarieName(?string $marieName): static
    {
        $this->marieName = $marieName;

        return $this;
    }

    public function getMarieEmail(): ?string
    {
        return $this->marieEmail;
    }

    public function setMarieEmail(?string $marieEmail): static
    {
        $this->marieEmail = $marieEmail;

        return $this;
    }

    public function getMarieTelephone(): ?string
    {
        return $this->marieTelephone;
    }

    public function setMarieTelephone(?string $marieTelephone): static
    {
        $this->marieTelephone = $marieTelephone;

        return $this;
    }

    public function getMarieAddressLine1(): ?string
    {
        return $this->marieAddressLine1;
    }

    public function setMarieAddressLine1(?string $marieAddressLine1): static
    {
        $this->marieAddressLine1 = $marieAddressLine1;

        return $this;
    }

    public function getMarieAddressLine2(): ?string
    {
        return $this->marieAddressLine2;
    }

    public function setMarieAddressLine2(?string $marieAddressLine2): static
    {
        $this->marieAddressLine2 = $marieAddressLine2;

        return $this;
    }

    public function getMarieAddressPostalCodeAndCity(): ?string
    {
        return $this->marieAddressPostalCodeAndCity;
    }

    public function setMarieAddressPostalCodeAndCity(?string $marieAddressPostalCodeAndCity): static
    {
        $this->marieAddressPostalCodeAndCity = $marieAddressPostalCodeAndCity;

        return $this;
    }

    public function getMarieeFirstName(): ?string
    {
        return $this->marieeFirstName;
    }

    public function setMarieeFirstName(?string $marieeFirstName): static
    {
        $this->marieeFirstName = $marieeFirstName;

        return $this;
    }

    public function getMarieeName(): ?string
    {
        return $this->marieeName;
    }

    public function setMarieeName(?string $marieeName): static
    {
        $this->marieeName = $marieeName;

        return $this;
    }

    public function getMarieeEmail(): ?string
    {
        return $this->marieeEmail;
    }

    public function setMarieeEmail(?string $marieeEmail): static
    {
        $this->marieeEmail = $marieeEmail;

        return $this;
    }

    public function getMarieeTelephone(): ?string
    {
        return $this->marieeTelephone;
    }

    public function setMarieeTelephone(?string $marieeTelephone): static
    {
        $this->marieeTelephone = $marieeTelephone;

        return $this;
    }

    public function getMarieeAddressLine1(): ?string
    {
        return $this->marieeAddressLine1;
    }

    public function setMarieeAddressLine1(?string $marieeAddressLine1): static
    {
        $this->marieeAddressLine1 = $marieeAddressLine1;

        return $this;
    }

    public function getMarieeAddressLine2(): ?string
    {
        return $this->marieeAddressLine2;
    }

    public function setMarieeAddressLine2(?string $marieeAddressLine2): static
    {
        $this->marieeAddressLine2 = $marieeAddressLine2;

        return $this;
    }

    public function getMarieeAddressPostalCodeAndCity(): ?string
    {
        return $this->marieeAddressPostalCodeAndCity;
    }

    public function setMarieeAddressPostalCodeAndCity(?string $marieeAddressPostalCodeAndCity): static
    {
        $this->marieeAddressPostalCodeAndCity = $marieeAddressPostalCodeAndCity;

        return $this;
    }

    public function getNotesMusiciens(): ?string
    {
        return $this->notesMusiciens;
    }

    public function setNotesMusiciens(?string $notesMusiciens): static
    {
        $this->notesMusiciens = $notesMusiciens;

        return $this;
    }
}
