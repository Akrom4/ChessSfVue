# Chess Learning Platform Implementation Guide

This document outlines the implementation of the Chess Learning Platform API, detailing the architecture decisions, entity structures, and endpoint implementations for both puzzle training and course management functionality.

## Database Schema

### User Entity

The `User` entity represents platform users with the following properties:

- `id` (int): Unique identifier
- `username` (string): User's username
- `email` (string): User's email
- `password` (string): Hashed password
- `roles` (array): User roles (ROLE_USER, ROLE_ADMIN)
- `created_at` (datetime): Creation timestamp
- `updated_at` (datetime): Last update timestamp
- `userCourses` (OneToMany): Courses the user is enrolled in

### Course Entity

The `Courses` entity represents chess courses with the following properties:

- `id` (int): Unique identifier
- `title` (string): Course title
- `description` (string): Course description
- `image` (string): Course image filename
- `author` (string): Course author
- `colorside` (string): Which side the student plays (white/black)
- `difficulty` (enum): Course difficulty level
- `createdat` (datetime): Creation timestamp
- `updatedat` (datetime): Last update timestamp
- `chapters` (OneToMany): Chapters belonging to this course
- `userCourses` (OneToMany): Users enrolled in this course

### Chapter Entity

The `Chapter` entity represents lessons within courses:

- `id` (int): Unique identifier
- `title` (string): Chapter title
- `course` (ManyToOne): Parent course
- `pgndata` (array): Chess position data
- `rawpgn` (text): Raw PGN notation

### UserCourses Entity

The `UserCourses` entity tracks user enrollment and progress:

- `id` (int): Unique identifier
- `userid` (ManyToOne): User enrolled in course
- `courseid` (ManyToOne): Course the user is enrolled in
- `completedChapters` (array): Chapters completed by the user
- `progress` (int): Overall progress percentage

### Puzzle Entity

The `Puzzle` entity represents chess puzzles with the following properties:

- `id` (string): Unique identifier for the puzzle
- `fen` (string): FEN notation of the puzzle position
- `moves` (string): Puzzle solution in algebraic notation
- `rating` (int): Difficulty rating of the puzzle
- `ratingDeviation` (int): Statistical confidence in the rating
- `popularity` (int): Popularity score based on user feedback
- `nbPlays` (int): Number of times the puzzle has been played
- `themes` (ManyToMany): Collection of chess themes associated with the puzzle
- `gameUrl` (string): URL to the original game
- `openingTags` (string): Opening classification

### Theme Entity

The `Theme` entity represents puzzle themes/tags:

- `id` (int): Unique identifier
- `name` (string): Theme name (e.g., "middlegame", "fork", "mate in 2")

## API Design

The API follows RESTful principles with custom endpoints for specific functionalities.

### Authentication Implementation

The API uses JWT authentication with lexik/jwt-authentication-bundle:

```yaml
# security.yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: true
            jwt: ~
```

Token generation endpoint:
```
POST /api/authentication_token
```

### Course Management Endpoints

#### 1. Courses Collection

Implementation using API Platform:

```php
#[ApiResource( 
    operations: [
        new Get(),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: [
        'groups' => ['course:read'],
        'enable_max_depth' => true
    ],
    denormalizationContext: [
        'groups' => ['course:write']
    ]
)]
#[GetCollection]
class Courses
```

#### 2. Course Image Upload

Custom controller for handling file uploads:

```php
#[Route('/api/courses/{id}/upload', name: 'api_course_image_upload', methods: ['POST', 'OPTIONS'])]
public function uploadImage(
    Request $request, 
    ?Courses $course = null, 
    ?EntityManagerInterface $entityManager = null,
    ?SerializerInterface $serializer = null
): Response
```

#### 3. Chapter Management

API Platform configuration with nested routes:

```php
#[GetCollection(
    uriTemplate: '/courses/{courseId}/chapters',
    uriVariables: [
        'courseId' => new Link(
            fromProperty: 'chapters',
            fromClass: Courses::class
        )
    ]
)]
#[Get(
    uriTemplate: '/courses/{courseId}/chapters/{id}',
    uriVariables: [
        'id' => new Link(
            fromClass: Chapter::class,
            identifiers: ['id']
        ),
        'courseId' => new Link(
            fromProperty: 'chapters',
            fromClass: Courses::class
        )
    ]
)]
class Chapter
```

#### 4. User Course Progress Tracking

Custom controllers:

```php
#[Route('/courses/{id}/following', name: 'check_course_following', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function checkFollowingStatus(Courses $course): JsonResponse

#[Route('/courses/{id}/my-chapters', name: 'get_followed_course_chapters', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function getChaptersForFollowedCourse(Courses $course): JsonResponse
```

### Puzzle Endpoints Implementation

#### 1. Individual Puzzle Retrieval

Implemented using API Platform:

```php
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['puzzle:read']],
    denormalizationContext: ['groups' => ['puzzle:write']]
)]
class Puzzle
```

#### 2. Random Puzzle Endpoint

Custom controller for fetching random puzzles:

```php
#[Route('/api/puzzles/random', name: 'get_random_puzzle', methods: ['GET'])]
public function __invoke(Request $request): JsonResponse
{
    // Extract parameters and fetch a random puzzle
    $minRating = (int)$request->query->get('min_rating', 0);
    $maxRating = (int)$request->query->get('max_rating', 3000);
    $themes = $request->query->has('themes') ? 
        explode(',', $request->query->get('themes')) : null;
    
    $puzzle = $this->puzzleRepository->findRandomPuzzleInRatingRange(
        $minRating, 
        $maxRating, 
        $themes
    );
    
    // Return serialized response
}
```

#### 3. Puzzles By Themes Endpoint

Controller for filtering puzzles by themes with pagination:

```php
#[Route('/api/puzzles/by-themes', name: 'get_puzzles_by_themes', methods: ['GET'])]
public function __invoke(Request $request): JsonResponse
{
    // Extract themes, rating range, and pagination parameters
    $themes = explode(',', $request->query->get('themes', ''));
    $minRating = (int)$request->query->get('min_rating', 0);
    $maxRating = (int)$request->query->get('max_rating', 3000);
    $page = max(1, (int)$request->query->get('page', 1));
    $limit = max(1, min(50, (int)$request->query->get('limit', 20)));
    
    // Get and return paginated results
    $result = $this->puzzleRepository->findPuzzlesByThemes(
        $themes,
        $minRating,
        $maxRating,
        $page,
        $limit
    );
    
    // Return serialized response with pagination metadata
}
```

## Data Migration

The project included a migration from a legacy format (comma-separated themes) to a normalized database structure. The migration involved:

1. Creating the new `theme` table
2. Creating the `puzzle_theme` junction table
3. Implementing a command to migrate data:
   ```
   php bin/console app:migrate-themes
   ```

The migration process maintained data integrity by:
- First extracting unique themes from all puzzles
- Creating `Theme` entities for each unique theme
- Establishing relationships between puzzles and their corresponding themes
- Verifying the migration was successful before removing legacy data

## Repository Implementations

### CoursesRepository

Specialized methods for course-related queries:

```php
public function findCourseWithChapters(int $id): ?Courses
{
    return $this->createQueryBuilder('c')
        ->leftJoin('c.chapters', 'ch')
        ->addSelect('ch')
        ->where('c.id = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getOneOrNullResult();
}
```

### UserCoursesRepository

Methods for managing user progress:

```php
public function findUserCourseProgress(User $user, Courses $course): ?UserCourses
{
    return $this->createQueryBuilder('uc')
        ->where('uc.userid = :user')
        ->andWhere('uc.courseid = :course')
        ->setParameter('user', $user)
        ->setParameter('course', $course)
        ->getQuery()
        ->getOneOrNullResult();
}
```

### PuzzleRepository

Methods for puzzle retrieval and filtering:

```php
public function findRandomPuzzleInRatingRange(int $minRating, int $maxRating, ?array $themes = null): ?Puzzle
{
    $qb = $this->createQueryBuilder('p')
        ->where('p.rating BETWEEN :minRating AND :maxRating')
        ->setParameter('minRating', $minRating)
        ->setParameter('maxRating', $maxRating);
    
    if ($themes && count($themes) > 0) {
        $qb->innerJoin('p.themes', 't')
           ->andWhere('t.name IN (:themes)')
           ->setParameter('themes', $themes);
    }
    
    // Order by random and limit to 1
    $qb->orderBy('RAND()')
       ->setMaxResults(1);
    
    return $qb->getQuery()->getOneOrNullResult();
}
```

## Serialization

API responses are properly serialized using Symfony's serializer with normalization groups:

```php
// Course Entity
#[Groups(['course:read', 'course:write', 'user:read', 'user_course:read'])]
private ?int $id = null;

// Chapter Entity
#[Groups(['chapter:read', 'chapter:write'])]
private ?string $title = null;

// Puzzle Entity
#[Groups(['puzzle:read'])]
private ?string $id = null;
```

Groups ensure proper nesting and prevent circular references.

## File Uploads

Course images are handled using VichUploaderBundle:

```php
#[Vich\Uploadable]
class Courses
{
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: "course_images", fileNameProperty: "image")]
    private ?File $imageFile = null;
    
    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedat = new \DateTimeImmutable();
        }
        return $this;
    }
}
```

## Testing

A comprehensive Python testing tool was developed to verify API functionality:

- `/tools/puzzles/tests/api_tester.py`: Main testing script
- `/tools/puzzles/tests/config.py`: Configuration
- `/tools/puzzles/tests/README.md`: Usage documentation

The test tool provides:
- Individual endpoint testing
- Performance testing
- Detailed logging
- Error handling with debugging information

## Security

API endpoints are secured using a combination of:

1. JWT authentication for user identification
2. Role-based access control:
   ```php
   #[Post(security: "is_granted('ROLE_ADMIN')")]
   ```

3. Attribute-based security:
   ```php
   #[IsGranted('ROLE_USER')]
   public function checkFollowingStatus(Courses $course): JsonResponse
   ```

## Optimization Considerations

1. Database indexes:
   ```php
   #[ORM\Index(columns: ["rating"], name: "rating_idx")]
   ```

2. Batch processing for large datasets
3. Pagination to limit result sizes
4. Proper relation management with appropriate join types
5. Max depth settings to control serialization depth:
   ```php
   #[MaxDepth(1)]
   private Collection $chapters;
   ``` 