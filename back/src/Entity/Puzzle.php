<?php

namespace App\Entity;

use App\Repository\PuzzleRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\GetRandomPuzzleController;
use App\Controller\GetPuzzlesByThemesController;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PuzzleRepository::class)]
#[ORM\Index(columns: ["rating"], name: "rating_idx")]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/puzzles/{id}',
            name: 'puzzle_item',
            requirements: ['id' => '^[A-Za-z0-9]{5}$']
        ),
        new Get(
            uriTemplate: '/puzzles/random',
            provider: GetRandomPuzzleController::class,
            normalizationContext: ['groups' => ['puzzle:read']],
            name: 'get_random_puzzle'
        ),
        new GetCollection(
            uriTemplate: '/puzzles/by-themes',
            provider: GetPuzzlesByThemesController::class,
            normalizationContext: ['groups' => ['puzzle:read']],
            name: 'get_puzzles_by_themes'
        ),
    ],
    normalizationContext: ['groups' => ['puzzle:read']],
    denormalizationContext: ['groups' => ['puzzle:write']]
)]
class Puzzle
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10, options: ['collation' => 'utf8mb4_bin'])]
    #[Groups(['puzzle:read'])]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?string $fen = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?string $moves = null;

    #[ORM\Column(type: 'smallint', options: ["unsigned" => true])]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?int $rating = null;

    #[ORM\Column(type: 'smallint', options: ["unsigned" => true])]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?int $ratingDeviation = null;

    #[ORM\Column(type: 'smallint', options: ["unsigned" => true])]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?int $popularity = null;

    #[ORM\Column(type: 'integer', options: ["unsigned" => true])]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?int $nbPlays = null;

    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'puzzles')]
    #[ORM\JoinTable(name: 'puzzle_theme')]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private Collection $themes;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?string $gameUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['puzzle:read', 'puzzle:write'])]
    private ?string $openingTags = null;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getFen(): ?string
    {
        return $this->fen;
    }

    public function setFen(string $fen): self
    {
        $this->fen = $fen;
        return $this;
    }

    public function getMoves(): ?string
    {
        return $this->moves;
    }

    public function setMoves(string $moves): self
    {
        $this->moves = $moves;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getRatingDeviation(): ?int
    {
        return $this->ratingDeviation;
    }

    public function setRatingDeviation(int $ratingDeviation): self
    {
        $this->ratingDeviation = $ratingDeviation;
        return $this;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): self
    {
        $this->popularity = $popularity;
        return $this;
    }

    public function getNbPlays(): ?int
    {
        return $this->nbPlays;
    }

    public function setNbPlays(int $nbPlays): self
    {
        $this->nbPlays = $nbPlays;
        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
            $theme->addPuzzle($this);
        }
        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        if ($this->themes->removeElement($theme)) {
            $theme->removePuzzle($this);
        }
        return $this;
    }

    public function getGameUrl(): ?string
    {
        return $this->gameUrl;
    }

    public function setGameUrl(?string $gameUrl): self
    {
        $this->gameUrl = $gameUrl;
        return $this;
    }

    public function getOpeningTags(): ?string
    {
        return $this->openingTags;
    }

    public function setOpeningTags(?string $openingTags): self
    {
        $this->openingTags = $openingTags;
        return $this;
    }
} 