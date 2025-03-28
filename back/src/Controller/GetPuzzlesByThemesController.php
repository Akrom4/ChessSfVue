<?php

namespace App\Controller;

use App\Repository\PuzzleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class GetPuzzlesByThemesController extends AbstractController implements ProviderInterface
{
    public function __construct(private PuzzleRepository $puzzleRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var Request $request */
        $request = $context['request'];

        $themesString = $request->query->get('themes', '');
        $themesArray = array_filter(array_map('trim', explode(',', $themesString)));

        if (empty($themesArray)) {
            throw new BadRequestHttpException('At least one valid theme must be provided.');
        }

        $minRating = max(0, (int)$request->query->get('min_rating', 0));
        $maxRating = min(3500, (int)$request->query->get('max_rating', 3000));

        if ($minRating > $maxRating) {
            throw new BadRequestHttpException('Min rating cannot be greater than max rating.');
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(50, (int)$request->query->get('limit', 20)));

        $result = $this->puzzleRepository->findPuzzlesByThemes(
            $themesArray,
            $minRating,
            $maxRating,
            $page,
            $limit
        );

        if (empty($result['puzzles'])) {
            throw new NotFoundHttpException('No puzzles found with the specified criteria.');
        }

        return [
            'items' => $result['puzzles'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
        ]; // API Platform auto-serialization
    }
}
