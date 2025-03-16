<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\UserCourses;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        // No need to add the restriction here as it is already handled in the GET operation security
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only filter UserCourses entity
        if ($resourceClass !== UserCourses::class) {
            return;
        }

        // Get current user
        $user = $this->security->getUser();
        
        // If not logged in or no user found, return no results (should not happen due to security)
        if (!$user) {
            $queryBuilder->andWhere('1 = 0');
            return;
        }

        // Add condition to only show current user's courses
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere(sprintf('%s.userid = :current_user', $rootAlias));
        $queryBuilder->setParameter('current_user', $user);
    }
} 