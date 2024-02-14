<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Task;
use App\Security\CurrentlyLoggedInUser;
use Doctrine\ORM\QueryBuilder;

final readonly class OnlyAllowTasksOwnedCurrentUser implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private CurrentlyLoggedInUser $currentlyLoggedInUser,
    ) {
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        if (!$this->supported($resourceClass)) {
            return;
        }

        $this->apply($queryBuilder, $queryNameGenerator);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if (!$this->supported($resourceClass)) {
            return;
        }

        $this->apply($queryBuilder, $queryNameGenerator);
    }

    private function supported($resourceClass): bool
    {
        return $resourceClass === Task::class;
    }

    private function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator): void
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName('current_user');

        $queryBuilder
            ->andWhere(sprintf('%s.owner = :%s', $alias, $parameterName))
            ->setParameter($parameterName, $this->currentlyLoggedInUser->get());
    }
}
