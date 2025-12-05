<?php

namespace App\Entity;

use App\Repository\WeddingSongSelectionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Wedding;
use App\Entity\SongType;
use App\Entity\Song;

#[ORM\Entity(repositoryClass: WeddingSongSelectionRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_wedding_type', columns: ['wedding_id', 'song_type_id'])]
class WeddingSongSelection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'songSelections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Wedding $wedding = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SongType $songType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Song $song = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $validatedByMusician = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $validatedByParish = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personneEnCharge = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWedding(): ?Wedding
    {
        return $this->wedding;
    }

    public function setWedding(?Wedding $wedding): self
    {
        $this->wedding = $wedding;

        return $this;
    }

    public function getSongType(): ?SongType
    {
        return $this->songType;
    }

    public function setSongType(?SongType $songType): self
    {
        $this->songType = $songType;

        return $this;
    }

    public function getSong(): ?Song
    {
        return $this->song;
    }

    public function setSong(?Song $song): self
    {
        $this->song = $song;

        return $this;
    }

    public function isValidatedByMusician(): bool
    {
        return $this->validatedByMusician;
    }

    public function setValidatedByMusician(bool $validated): self
    {
        $this->validatedByMusician = $validated;

        return $this;
    }

    public function isValidatedByParish(): bool
    {
        return $this->validatedByParish;
    }

    public function setValidatedByParish(bool $validated): self
    {
        $this->validatedByParish = $validated;

        return $this;
    }

    public function getPersonneEnCharge(): ?string
    {
        return $this->personneEnCharge;
    }

    public function setPersonneEnCharge(?string $personneEnCharge): static
    {
        $this->personneEnCharge = $personneEnCharge;

        return $this;
    }
}
