<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class UserCoursesFilter extends AbstractFilter
{
    private $security;

    public function __construct(
        ManagerRegistry $managerRegistry, 
        Security $security, 
        ?LoggerInterface $logger = null, 
        ?array $properties = null, 
        ?NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
        $this->security = $security;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        // Only apply this filter on the 'UserCourses' entity
        if ($resourceClass !== \App\Entity\UserCourses::class) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        // Check if we're filtering by 'current_user' 
        if ($property === 'current_user' && $value === 'true') {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.userid = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
        }
    }

    // This function is only used to display the filter in the API documentation
    public function getDescription(string $resourceClass): array
    {
        if ($resourceClass !== \App\Entity\UserCourses::class) {
            return [];
        }

        return [
            'current_user' => [
                'property' => 'current_user',
                'type' => 'boolean',
                'required' => false,
                'description' => 'Filter to get only the current user courses (true/false)',
                'swagger' => [
                    'description' => 'Filter to get only the current user courses',
                    'name' => 'current_user',
                    'type' => 'boolean'
                ],
            ],
        ];
    }
} 