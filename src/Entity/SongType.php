<?php

namespace App\Entity;

use App\Repository\SongTypeRepository;
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

    /**
     * @var Collection<int, Song>
     */
    #[ORM\OneToMany(targetEntity: Song::class, mappedBy: 'type')]
    private Collection $songs;

    #[ORM\Column]
    private ?bool $messe = null;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
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

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->setType($this);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            // set the owning side to null (unless already changed)
            if ($song->getType() === $this) {
                $song->setType(null);
            }
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
