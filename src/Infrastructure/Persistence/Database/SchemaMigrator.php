<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

use PDO;

class SchemaMigrator
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, string>
     */
    public function migrate(): array
    {
        $statements = SchemaDefinition::getCreateTableStatements();
        $migrated = [];

        foreach (SchemaDefinition::TABLE_ORDER as $tableName) {
            if (!isset($statements[$tableName])) {
                continue;
            }

            $this->pdo->exec($statements[$tableName]);
            $migrated[] = $tableName;
        }

        return $migrated;
    }

    /**
     * @return array<int, string>
     */
    public function dropAll(): array
    {
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');

        $dropped = [];
        $reversed = array_reverse(SchemaDefinition::TABLE_ORDER);

        foreach ($reversed as $tableName) {
            $this->pdo->exec(sprintf('DROP TABLE IF EXISTS %s;', $tableName));
            $dropped[] = $tableName;
        }

        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');

        return $dropped;
    }
}
