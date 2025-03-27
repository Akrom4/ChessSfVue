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

#[Route('/api/puzzles')]
class PuzzleController extends AbstractController
{
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
     */
    #[Route('/random', name: 'puzzle_random', methods: ['GET'])]
    public function getRandomPuzzle(Request $request): JsonResponse
    {
        // Extract and validate parameters
        $minRating = (int)$request->query->get('min_rating', 0);
        $maxRating = (int)$request->query->get('max_rating', 3000);
        
        // Validate rating range
        if ($minRating < 0) {
            $minRating = 0;
        }
        
        if ($maxRating > 3500) {
            $maxRating = 3500;
        }
        
        if ($minRating > $maxRating) {
            return new JsonResponse(['error' => 'Min rating cannot be greater than max rating'], Response::HTTP_BAD_REQUEST);
        }
        
        // Extract themes if provided
        $themes = null;
        if ($request->query->has('themes')) {
            $themesString = $request->query->get('themes');
            $themes = explode(',', $themesString);
            // Trim whitespace from theme names
            $themes = array_map('trim', $themes);
            // Filter out empty values
            $themes = array_filter($themes);
        }
        
        // Get a random puzzle
        $puzzle = $this->puzzleRepository->findRandomPuzzleInRatingRange($minRating, $maxRating, $themes);
        
        if (!$puzzle) {
            return new JsonResponse(['error' => 'No puzzles found with the specified criteria'], Response::HTTP_NOT_FOUND);
        }
        
        // Serialize the puzzle with the 'puzzle:read' group
        $puzzleData = $this->serializer->serialize($puzzle, 'json', ['groups' => ['puzzle:read']]);
        
        return new JsonResponse($puzzleData, Response::HTTP_OK, [], true);
    }

    /**
     * Get puzzles filtered by themes with pagination.
     */
    #[Route('/by-themes', name: 'puzzles_by_themes', methods: ['GET'])]
    public function getPuzzlesByThemes(Request $request): JsonResponse
    {
        // Extract and validate parameters
        $themes = $request->query->get('themes', '');
        if (empty($themes)) {
            return new JsonResponse(['error' => 'At least one theme must be provided'], Response::HTTP_BAD_REQUEST);
        }
        
        $themesArray = explode(',', $themes);
        // Trim whitespace from theme names
        $themesArray = array_map('trim', $themesArray);
        // Filter out empty values
        $themesArray = array_filter($themesArray);
        
        if (empty($themesArray)) {
            return new JsonResponse(['error' => 'At least one valid theme must be provided'], Response::HTTP_BAD_REQUEST);
        }
        
        // Extract rating range
        $minRating = (int)$request->query->get('min_rating', 0);
        $maxRating = (int)$request->query->get('max_rating', 3000);
        
        // Validate rating range
        if ($minRating < 0) {
            $minRating = 0;
        }
        
        if ($maxRating > 3500) {
            $maxRating = 3500;
        }
        
        if ($minRating > $maxRating) {
            return new JsonResponse(['error' => 'Min rating cannot be greater than max rating'], Response::HTTP_BAD_REQUEST);
        }
        
        // Extract pagination parameters
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(50, (int)$request->query->get('limit', 20))); // Between 1 and 50
        
        // Get puzzles by themes
        $result = $this->puzzleRepository->findPuzzlesByThemes(
            $themesArray,
            $minRating,
            $maxRating,
            $page,
            $limit
        );
        
        if (empty($result['puzzles'])) {
            return new JsonResponse(['error' => 'No puzzles found with the specified criteria'], Response::HTTP_NOT_FOUND);
        }
        
        // Serialize the puzzles with the 'puzzle:read' group
        $puzzlesData = $this->serializer->serialize([
            'items' => $result['puzzles'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
        ], 'json', ['groups' => ['puzzle:read']]);
        
        return new JsonResponse($puzzlesData, Response::HTTP_OK, [], true);
    }
} 