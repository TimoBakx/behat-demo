<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\Persistence\ManagerRegistry;

final readonly class ApplicationContext implements Context
{
    private const DOCTRINE_MIGRATIONS_TABLE = 'doctrine_migration_versions';

    public function __construct(
        private ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @BeforeScenario @resetDatabase
     *
     * @throws DBALException
     * @throws \RuntimeException
     */
    public function resetDatabase(): void
    {
        /** @var Connection $connection */
        $connection = $this->doctrine->getConnection();
        $platform = $connection->getDatabasePlatform();
        $tables = array_diff($connection->createSchemaManager()->listTableNames(), [self::DOCTRINE_MIGRATIONS_TABLE]);

        if ($platform === null) {
            throw new \RuntimeException('No database platform found');
        }

        if ($platform instanceof MySQLPlatform) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
            foreach ($tables as $table) {
                $connection->executeStatement($platform->getTruncateTableSQL($table));
            }
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

        } elseif ($platform instanceof PostgreSQLPlatform) {
            $connection->beginTransaction();
            $connection->executeQuery('SET session_replication_role = "replica";');

            foreach ($tables as $table) {
                $tableIdentifier = (new Identifier($table))->getQuotedName($platform);

                $connection->executeQuery(\sprintf('ALTER TABLE %s DISABLE TRIGGER ALL', $tableIdentifier));
                $connection->executeQuery(\sprintf('TRUNCATE %s CASCADE', $tableIdentifier));
                $connection->executeQuery(\sprintf('ALTER TABLE %s ENABLE TRIGGER ALL', $tableIdentifier));
            }

            $connection->executeQuery('SET session_replication_role = "origin";');
            $connection->commit();
        }
    }

}
