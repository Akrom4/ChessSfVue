<?php

namespace App\Repository;

use App\Entity\Puzzle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Puzzle>
 *
 * @method Puzzle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Puzzle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Puzzle[]    findAll()
 * @method Puzzle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuzzleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Puzzle::class);
    }

    public function save(Puzzle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Puzzle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Find a random puzzle within a specific rating range
     * Optimized for very large datasets
     * 
     * @param int $minRating The minimum rating value
     * @param int $maxRating The maximum rating value
     * @param array|null $themes Optional array of theme names to filter by
     * @return Puzzle|null A random puzzle or null if none found
     */
    public function findRandomPuzzleInRatingRange(int $minRating, int $maxRating, ?array $themes = null): ?Puzzle
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Base query to get a random puzzle ID
        if ($themes && count($themes) > 0) {
            // With theme filtering
            $sql = "
                SELECT p.id
                FROM puzzle p
                JOIN puzzle_theme pt ON p.id = pt.puzzle_id
                JOIN theme t ON pt.theme_id = t.id
                WHERE p.rating BETWEEN :min_rating AND :max_rating
                AND t.name IN (:themes)
                ORDER BY RAND()
                LIMIT 1
            ";
            
            // Prepare the query
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindValue('min_rating', $minRating);
            $stmt->bindValue('max_rating', $maxRating);
            $stmt->bindValue('themes', $themes, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        } else {
            // Without theme filtering - much faster
            $sql = "
                SELECT id
                FROM puzzle
                WHERE rating BETWEEN :min_rating AND :max_rating
                ORDER BY RAND()
                LIMIT 1
            ";
            
            // Prepare the query
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindValue('min_rating', $minRating);
            $stmt->bindValue('max_rating', $maxRating);
        }
        
        // Execute the query
        $result = $stmt->executeQuery();
        $puzzleId = $result->fetchOne();
        
        if (!$puzzleId) {
            return null;
        }
        
        // Get the full puzzle entity
        return $this->find($puzzleId);
    }

    /**
     * Find puzzles filtered by themes and rating range with pagination
     * 
     * @param array $themes Array of theme names to filter by
     * @param int $minRating The minimum rating value
     * @param int $maxRating The maximum rating value
     * @param int $page The page number (starting from 1)
     * @param int $limit The number of puzzles per page
     * @return array An array containing the paginated puzzles and total count
     */
    public function findPuzzlesByThemes(
        array $themes, 
        int $minRating = 0, 
        int $maxRating = 3500, 
        int $page = 1, 
        int $limit = 20
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->join('p.themes', 't')
            ->where('t.name IN (:themes)')
            ->andWhere('p.rating >= :minRating')
            ->andWhere('p.rating <= :maxRating')
            ->setParameter('themes', $themes)
            ->setParameter('minRating', $minRating)
            ->setParameter('maxRating', $maxRating)
            ->orderBy('p.popularity', 'DESC');
            
        // Count total results before pagination
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT p.id)');
        $totalCount = $countQb->getQuery()->getSingleScalarResult();
        
        // Apply pagination
        $offset = ($page - 1) * $limit;
        $qb->setMaxResults($limit)
           ->setFirstResult($offset);
           
        return [
            'puzzles' => $qb->getQuery()->getResult(),
            'total' => $totalCount,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalCount / $limit)
        ];
    }
} 