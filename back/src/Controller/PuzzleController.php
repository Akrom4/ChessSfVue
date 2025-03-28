<?php

namespace App\Controller;

use App\Entity\Puzzle;
use App\Repository\PuzzleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;

// DEPRECATED: This controller is no longer used. See GetRandomPuzzleController and GetPuzzlesByThemesController instead.
// This file can be removed after API functionality is verified to be working correctly.
#[AsController]
class PuzzleController extends AbstractController
{
    /*
    private PuzzleRepository $puzzleRepository;
    private SerializerInterface $serializer;

    public function __construct(
        PuzzleRepository $puzzleRepository,
        SerializerInterface $serializer
    ) {
        $this->puzzleRepository = $puzzleRepository;
        $this->serializer = $serializer;
    }

    /**
     * Get a random puzzle within a rating range.
     * Optional theme filtering is supported.
     * 
     * @deprecated Use GetRandomPuzzleController instead
     *-/
    #[Route('/api/puzzles/random', name: 'puzzle_random', methods: ['GET'])]
    public function getRandomPuzzle(Request $request): JsonResponse
    {
        // Implementation removed - see GetRandomPuzzleController
        return new JsonResponse(['error' => 'Method deprecated'], Response::HTTP_GONE);
    }

    /**
     * Get puzzles filtered by themes with pagination.
     * 
     * @deprecated Use GetPuzzlesByThemesController instead
     *-/
    #[Route('/api/puzzles/by-themes', name: 'puzzles_by_themes', methods: ['GET'])]
    public function getPuzzlesByThemes(Request $request): JsonResponse
    {
        // Implementation removed - see GetPuzzlesByThemesController
        return new JsonResponse(['error' => 'Method deprecated'], Response::HTTP_GONE);
    }
    */
} 