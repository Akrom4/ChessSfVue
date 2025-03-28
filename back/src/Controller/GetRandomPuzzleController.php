<?php

namespace App\Controller;

use App\Entity\Puzzle;
use App\Repository\PuzzleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class GetRandomPuzzleController extends AbstractController implements ProviderInterface
{
    public function __construct(private PuzzleRepository $puzzleRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Puzzle
    {
        /** @var Request $request */
        $request = $context['request'];

        $minRating = max(0, (int)$request->query->get('min_rating', 0));
        $maxRating = min(3500, (int)$request->query->get('max_rating', 3000));

        if ($minRating > $maxRating) {
            throw new BadRequestHttpException('Min rating cannot be greater than max rating.');
        }

        $themesString = $request->query->get('themes', '');
        $themes = array_filter(array_map('trim', explode(',', $themesString)));

        $puzzle = $this->puzzleRepository->findRandomPuzzleInRatingRange($minRating, $maxRating, $themes ?: null);

        if (!$puzzle) {
            throw new NotFoundHttpException('No puzzles found with the specified criteria.');
        }

        return $puzzle; // API Platform auto-serialization
    }
}
