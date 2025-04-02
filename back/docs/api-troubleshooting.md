# Chess Learning Platform Troubleshooting Guide

This document provides solutions for common issues that may arise when using or developing the Chess Learning Platform API.

## API Connectivity Issues

### 404 Not Found Errors

If you're encountering 404 errors when accessing API endpoints:

1. **Check Route Registration**
   ```
   php bin/console debug:router | grep api
   ```
   Ensure your routes are properly registered.

2. **Verify Controller Naming**
   Make sure that controllers use the proper attributes:
   ```php
   #[AsController]
   class GetRandomPuzzleController extends AbstractController implements ProviderInterface
   ```

3. **API Platform Configuration**
   If using API Platform, check that operations are correctly defined:
   ```php
   #[ApiResource(
       operations: [
           new Get(
               uriTemplate: '/puzzles/{id}',
               name: 'puzzle_item'
           ),
           // other operations
       ]
   )]
   ```

4. **Clear Cache**
   ```
   php bin/console cache:clear
   ```

### 500 Internal Server Errors

1. **Check Exception Logs**
   ```
   tail -f var/log/dev.log
   ```

2. **Database Connectivity**
   Verify database connection parameters in `.env.local`.

3. **Repository Methods**
   Ensure repository methods have proper error handling.

## Authentication Issues

### JWT Token Problems

1. **Check JWT Configuration**
   Verify that your JWT keys are properly generated and configured:
   ```
   php bin/console lexik:jwt:check-config
   ```

2. **Validate JWT Tokens**
   Use a tool like [jwt.io](https://jwt.io/) to verify your tokens.

3. **Check User Entity**
   Ensure your User entity correctly implements `UserInterface` and `PasswordAuthenticatedUserInterface`.

4. **Test Authentication Endpoint**
   ```
   curl -X POST -H "Content-Type: application/json" -d '{"username":"admin","password":"password"}' http://localhost:8000/api/authentication_token
   ```

## Course Management Issues

### Course Image Upload Failures

1. **Check VichUploader Configuration**
   Verify your `vich_uploader.yaml` configuration:
   ```yaml
   course_images:
       uri_prefix: /images/courses
       upload_destination: '%kernel.project_dir%/public/images/courses'
   ```

2. **Directory Permissions**
   Ensure upload directories have proper write permissions.

3. **Debug File Uploads**
   Add temporary debug code to the controller:
   ```php
   dump($request->files);
   ```

### Course-Chapter Relationship Issues

1. **Check Entity Relationships**
   Ensure bidirectional relationships are properly configured:
   ```php
   // In Courses entity
   #[ORM\OneToMany(mappedBy: 'course', targetEntity: Chapter::class)]
   private Collection $chapters;
   
   // In Chapter entity
   #[ORM\ManyToOne(inversedBy: 'chapters')]
   private ?Courses $course = null;
   ```

2. **Verify Serialization Groups**
   Check that your serialization groups allow for nested entities:
   ```php
   #[Groups(['course:read'])]
   #[MaxDepth(1)]
   private Collection $chapters;
   ```

## User Progress Tracking Issues

### Progress Not Saving

1. **Debug User Courses Entity**
   Check that the `UserCourses` entity can properly store completed chapters:
   ```php
   #[ORM\Column(nullable: true)]
   private array $completedChapters = [];
   ```

2. **Verify Controller Logic**
   Ensure that your controller properly updates the progress field:
   ```php
   $userCourse->setCompletedChapters($completedChapters);
   $userCourse->setProgress($progressPercentage);
   ```

3. **Check Transaction Management**
   Make sure your EntityManager is properly flushing:
   ```php
   $entityManager->persist($userCourse);
   $entityManager->flush();
   ```

## Data Migration Issues

### Issues with Theme Migration

If you encounter issues when migrating themes:

1. **Check Data Format**
   Verify if themes are space-separated or comma-separated in the legacy format.

2. **Run Migration in Verbose Mode**
   ```
   php bin/console app:migrate-themes -v
   ```

3. **Reset Migration (if needed)**
   ```sql
   -- SQL to reset migration (use with caution)
   TRUNCATE TABLE theme;
   TRUNCATE TABLE puzzle_theme;
   ```

## Performance Optimization

### Slow Response Times

1. **Add Database Indexes**
   ```php
   #[ORM\Index(columns: ["rating"], name: "rating_idx")]
   #[ORM\Index(columns: ["popularity"], name: "popularity_idx")]
   ```

2. **Use Query Caching**
   ```php
   $qb->getQuery()->setResultCacheId('puzzle_by_theme')
      ->setResultCacheLifetime(3600); // 1 hour
   ```

3. **Implement API Response Caching**
   Configure HTTP cache headers in `config/packages/api_platform.yaml`.

4. **Optimize Doctrine Queries**
   Avoid N+1 query problems by using eager loading:
   ```php
   $this->createQueryBuilder('c')
       ->leftJoin('c.chapters', 'ch')
       ->addSelect('ch')
   ```

### Memory Issues with Large Collections

1. **Implement Pagination**
   Always use pagination for large collections:
   ```php
   $qb->setFirstResult($offset)
      ->setMaxResults($limit);
   ```

2. **Use Iterative Processing**
   For batch operations, use iterative processing:
   ```php
   $q = $entityManager->createQuery('SELECT p FROM App\Entity\Puzzle p');
   $iterableResult = $q->iterate();
   foreach ($iterableResult as $row) {
       $puzzle = $row[0];
       // Process puzzle
       $entityManager->detach($puzzle);  // free memory
   }
   ```

## Testing Issues

### API Tester Script Failures

1. **Server Connectivity**
   Make sure the API server is running and accessible at the configured URL.

2. **Update URL in Configuration**
   Edit `tools/puzzles/tests/config.py` to point to the correct API URL.

3. **Check Permissions**
   Ensure the script has permission to write to the logs directory.

### PHPUnit Test Failures

1. **Database Configuration**
   Ensure you have a separate test database configured.

2. **Reset Database Between Tests**
   Use the `dama/doctrine-test-bundle` to reset the database between tests.

3. **Mock External Services**
   Use MockHandler for external API calls.

## Controller Implementation Notes

### Custom Provider Interface

If implementing the `ProviderInterface`:

```php
public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
{
    $request = $context['request'] ?? $this->container->get('request_stack')->getCurrentRequest();
    
    // Implementation details
    // ...
    
    return $result;
}
```

### Error Handling Best Practices

```php
try {
    // Operation that might fail
} catch (\Doctrine\DBAL\Exception $e) {
    // Database error
    throw new \Exception('Database error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
} catch (\Exception $e) {
    // General error
    throw new \Exception('An error occurred: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
}
```

## Common Symfony Commands

```bash
# Clear cache
php bin/console cache:clear

# Debug routes
php bin/console debug:router

# Create migration
php bin/console make:migration

# Apply migration
php bin/console doctrine:migrations:migrate

# Check security settings
php bin/console debug:config security

# Check API Platform settings
php bin/console debug:config api_platform

# Generate JWT keys
php bin/console lexik:jwt:generate-keypair

# List services
php bin/console debug:container --show-private
```

## File Upload Troubleshooting

```bash
# Check directory permissions
ls -la public/images/courses

# Set proper permissions
chmod -R 777 public/images/courses  # Not for production!

# Verify PHP settings
php -r "echo ini_get('upload_max_filesize'), PHP_EOL;"
php -r "echo ini_get('post_max_size'), PHP_EOL;"
```

## Additional Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [API Platform Documentation](https://api-platform.com/docs/)
- [Doctrine ORM Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [Lexik JWT Authentication Bundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
- [VichUploader Bundle](https://github.com/dustin10/VichUploaderBundle) 