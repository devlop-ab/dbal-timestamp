<?php

declare(strict_types=1);

namespace Devlop\DBAL\Types;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;
use Doctrine\DBAL\Types\PhpIntegerMappingType;
use Doctrine\DBAL\Types\Type;

final class Timestamp extends Type implements PhpDateTimeMappingType, PhpIntegerMappingType
{
    /**
     * The name of the custom type.
     *
     * @var string
     */
    public const NAME = 'timestamp';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) : string
    {
        $name = $platform->getName();

        switch ($name) {
            case 'mysql':
            case 'mysql2':
                return $this->getMySqlPlatformSQLDeclaration($fieldDeclaration);

            case 'postgresql':
            case 'pgsql':
            case 'postgres':
                return $this->getPostgresPlatformSQLDeclaration($fieldDeclaration);

            case 'mssql':
                return $this->getSqlServerPlatformSQLDeclaration($fieldDeclaration);

            case 'sqlite':
            case 'sqlite3':
                return $this->getSQLitePlatformSQLDeclaration($fieldDeclaration);

            default:
                throw new DBALException('Invalid platform: ' . $name);
        }
    }

    /**
     * Get the SQL declaration for MySQL.
     */
    private function getMySqlPlatformSQLDeclaration(array $fieldDeclaration) : string
    {
        $columnDefinition = 'TIMESTAMP';
        $currentDefinition = 'CURRENT_TIMESTAMP';

        if ($fieldDeclaration['precision']) {
            $columnDefinition .= '(' . (int) $fieldDeclaration['precision'] . ')';
            $currentDefinition .= '(' . (int) $fieldDeclaration['precision'] . ')';
        }

        $nullable = ($fieldDeclaration['notnull'] ?? false) === false;

        if ($nullable === true) {
            $columnDefinition .= ' NULL';
        }

        if ($fieldDeclaration['useCurrent'] ?? false) {
            // Not possible together with nullable === true atm, gets overwritten by Doctrine\DBAL\Platforms\AbstractPlatform::getColumnDeclarationSQL()
            // Throw exception?
            $columnDefinition .= ' DEFAULT ' . $currentDefinition;
        }

        if ($fieldDeclaration['useCurrentOnUpdate'] ?? false) {
            $columnDefinition .= ' ON UPDATE ' . $currentDefinition;
        }

        // dump([
        //     $columnDefinition => $fieldDeclaration,
        // ]);

        return $columnDefinition;
    }

    /**
     * Get the SQL declaration for PostgreSQL.
     */
    private function getPostgresPlatformSQLDeclaration(array $fieldDeclaration) : string
    {
        return 'TIMESTAMP(' . (int) $fieldDeclaration['precision'] . ')';
    }

    /**
     * Get the SQL declaration for SQL Server.
     */
    private function getSqlServerPlatformSQLDeclaration(array $fieldDeclaration) : string
    {
        return $fieldDeclaration['precision'] ?? false
            ? 'DATETIME2(' . $fieldDeclaration['precision'] . ')'
            : 'DATETIME';
    }

    /**
     * Get the SQL declaration for SQLite.
     */
    private function getSQLitePlatformSQLDeclaration(array $fieldDeclaration) : string
    {
        return 'DATETIME';
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return self::NAME;
    }
}
