<?php declare(strict_types=1);

namespace Myfav\CraftImport\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1744777769MyfavVerein extends MigrationStep
{
    /**
     * getCreationTimestamp
     *
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1744777769;
    }

    /**
     * update
     *
     * @param  Connection $connection
     * @return void
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            'CREATE TABLE IF NOT EXISTS `myfav_verein` (
            `id` BINARY(16) NOT NULL,
            `name` VARCHAR(256),
            `product_number_token` VARCHAR(8),
            `category_id` BINARY(16) DEFAULT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}