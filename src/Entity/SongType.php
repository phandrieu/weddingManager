<?php

namespace App\Entity;

use App\Repository\SongTypeRepository;
use App\Enum\CelebrationPeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SongTypeRepository::class)]
class SongType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Song::class, mappedBy: 'types')]
    private Collection $songs;

    #[ORM\Column]
    private ?bool $messe = null;
    #[ORM\Column(type: 'string', enumType: CelebrationPeriod::class, nullable: true)]
    private ?CelebrationPeriod $celebrationPeriod = null;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
    }
    public function getCelebrationPeriod(): ?CelebrationPeriod
    {
        return $this->celebrationPeriod;
    }
    public function setCelebrationPeriod(?CelebrationPeriod $celebrationPeriod): static
    {
        $this->celebrationPeriod = $celebrationPeriod;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): self
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->addType($this);
        }

        return $this;
    }

    public function removeSong(Song $song): self
    {
        if ($this->songs->removeElement($song)) {
            $song->removeType($this);
        }

        return $this;
    }

    public function isMesse(): ?bool
    {
        return $this->messe;
    }

    public function setMesse(bool $messe): static
    {
        $this->messe = $messe;

        return $this;
    }
}
