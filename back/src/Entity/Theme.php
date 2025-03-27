<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ORM\Table(name: 'theme')]
#[ORM\Index(columns: ['name'], name: 'theme_name_idx')]
#[ApiResource(
    operations: [
        new Get()
    ],
    normalizationContext: ['groups' => ['theme:read']],
    denormalizationContext: ['groups' => ['theme:write']]
)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['theme:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['theme:read', 'theme:write'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Puzzle::class, mappedBy: 'themes')]
    private Collection $puzzles;

    public function __construct()
    {
        $this->puzzles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Puzzle>
     */
    public function getPuzzles(): Collection
    {
        return $this->puzzles;
    }

    public function addPuzzle(Puzzle $puzzle): self
    {
        if (!$this->puzzles->contains($puzzle)) {
            $this->puzzles->add($puzzle);
            $puzzle->addTheme($this);
        }
        return $this;
    }

    public function removePuzzle(Puzzle $puzzle): self
    {
        if ($this->puzzles->removeElement($puzzle)) {
            $puzzle->removeTheme($this);
        }
        return $this;
    }
} 