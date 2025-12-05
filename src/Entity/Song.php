<?php

namespace App\Entity;

use App\Repository\SongRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SongRepository::class)]
#[Vich\Uploadable]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: SongType::class, inversedBy: 'songs')]
    private Collection $types;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $previewUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lyrics = null;

    /**
     * @var Collection<int, Wedding>
     */
    #[ORM\ManyToMany(targetEntity: Wedding::class, mappedBy: 'songs')]
    private Collection $weddings;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partitionPDFName = null;

    #[Vich\UploadableField(mapping: "song_pdf", fileNameProperty: "partitionPDFName")]
    private ?File $partitionPDFFile = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $suggestion = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $song = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lyricsAuthorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $musicAuthorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $editorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $interpretName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textRef = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $textTranslationName = null;

    #[ORM\ManyToOne(inversedBy: 'songsAdded')]
    private ?User $addedBy = null;

    #[ORM\ManyToOne(inversedBy: 'songsLastEdited')]
    private ?User $lastEditBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $addedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastEditAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $private = false;

    public function __construct()
    {
        $this->weddings = new ArrayCollection();
        $this->types = new ArrayCollection();
    }

    // --- Getters / Setters existants ---

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
 * @return Collection<int, SongType>
 */
public function getTypes(): Collection
{
    return $this->types;
}

public function addType(SongType $type): self
{
    if (!$this->types->contains($type)) {
        $this->types->add($type);
    }

    return $this;
}

public function removeType(SongType $type): self
{
    $this->types->removeElement($type);
    return $this;
}

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function setPreviewUrl(?string $previewUrl): static
    {
        $this->previewUrl = $previewUrl;
        return $this;
    }

    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    public function setLyrics(?string $lyrics): static
    {
        $this->lyrics = $lyrics;
        return $this;
    }

    /**
     * @return Collection<int, Wedding>
     */
    public function getWeddings(): Collection
    {
        return $this->weddings;
    }

    public function addWedding(Wedding $wedding): static
    {
        if (!$this->weddings->contains($wedding)) {
            $this->weddings->add($wedding);
            $wedding->addSong($this);
        }
        return $this;
    }

    public function removeWedding(Wedding $wedding): static
    {
        if ($this->weddings->removeElement($wedding)) {
            $wedding->removeSong($this);
        }
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // --- PDF management ---

    public function setPartitionPDFFile(?File $file = null): void
    {
        $this->partitionPDFFile = $file;

        if ($file) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPartitionPDFFile(): ?File
    {
        return $this->partitionPDFFile;
    }

    public function getPartitionPDFName(): ?string
    {
        return $this->partitionPDFName;
    }

    public function setPartitionPDFName(?string $partitionPDFName): static
    {
        $this->partitionPDFName = $partitionPDFName;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isSuggestion(): bool
    {
        return $this->suggestion;
    }

    public function setSuggestion(bool $suggestion): static
    {
        $this->suggestion = $suggestion;

        return $this;
    }

    public function isSong(): bool
    {
        return $this->song;
    }

    public function setSong(bool $song): static
    {
        $this->song = $song;

        return $this;
    }

    public function getLyricsAuthorName(): ?string
    {
        return $this->lyricsAuthorName;
    }

    public function setLyricsAuthorName(?string $lyricsAuthorName): static
    {
        $this->lyricsAuthorName = $lyricsAuthorName;

        return $this;
    }

    public function getMusicAuthorName(): ?string
    {
        return $this->musicAuthorName;
    }

    public function setMusicAuthorName(?string $musicAuthorName): static
    {
        $this->musicAuthorName = $musicAuthorName;

        return $this;
    }

    public function getEditorName(): ?string
    {
        return $this->editorName;
    }

    public function setEditorName(?string $editorName): static
    {
        $this->editorName = $editorName;

        return $this;
    }

    public function getInterpretName(): ?string
    {
        return $this->interpretName;
    }

    public function setInterpretName(?string $interpretName): static
    {
        $this->interpretName = $interpretName;

        return $this;
    }

    public function getTextRef(): ?string
    {
        return $this->textRef;
    }

    public function setTextRef(?string $textRef): static
    {
        $this->textRef = $textRef;

        return $this;
    }

    public function getTextTranslationName(): ?string
    {
        return $this->textTranslationName;
    }

    public function setTextTranslationName(?string $textTranslationName): static
    {
        $this->textTranslationName = $textTranslationName;

        return $this;
    }

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): static
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getLastEditBy(): ?User
    {
        return $this->lastEditBy;
    }

    public function setLastEditBy(?User $lastEditBy): static
    {
        $this->lastEditBy = $lastEditBy;

        return $this;
    }

    public function getAddedAt(): ?\DateTime
    {
        return $this->addedAt;
    }

    public function setAddedAt(?\DateTime $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getLastEditAt(): ?\DateTime
    {
        return $this->lastEditAt;
    }

    public function setLastEditAt(?\DateTime $lastEditAt): static
    {
        $this->lastEditAt = $lastEditAt;

        return $this;
    }

    public function isPrivate(): bool
    {
        return (bool) $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }
}