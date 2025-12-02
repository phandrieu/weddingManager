<?php

namespace App\Entity;

use App\Repository\CelebrationPeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CelebrationPeriodRepository::class)]
class CelebrationPeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(nullable: true)]
    private ?int $periodOrder = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    /**
     * @var Collection<int, SongType>
     */
    #[ORM\OneToMany(targetEntity: SongType::class, mappedBy: 'celebrationPeriod')]
    private Collection $songTypes;

    public function __construct()
    {
        $this->songTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPeriodOrder(): ?int
    {
        return $this->periodOrder;
    }

    public function setPeriodOrder(?int $periodOrder): static
    {
        $this->periodOrder = $periodOrder;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, SongType>
     */
    public function getSongTypes(): Collection
    {
        return $this->songTypes;
    }

    public function addSongType(SongType $songType): static
    {
        if (!$this->songTypes->contains($songType)) {
            $this->songTypes->add($songType);
            $songType->setCelebrationPeriod($this);
        }

        return $this;
    }

    public function removeSongType(SongType $songType): static
    {
        if ($this->songTypes->removeElement($songType)) {
            // set the owning side to null (unless already changed)
            if ($songType->getCelebrationPeriod() === $this) {
                $songType->setCelebrationPeriod(null);
            }
        }

        return $this;
    }
}
