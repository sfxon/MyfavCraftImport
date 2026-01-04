<?php declare(strict_types=1);

namespace Myfav\CraftImport\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1744777770MyfavVereinArticle extends MigrationStep
{
    /**
     * getCreationTimestamp
     *
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1744777770;
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
            'CREATE TABLE IF NOT EXISTS `myfav_verein_article` (
                `id` BINARY(16) NOT NULL,
                `myfav_verein_id` BINARY(16) DEFAULT NULL,
                `myfav_craft_import_article_id` BINARY(16) DEFAULT NULL,
                `custom_product_settings` JSON DEFAULT NULL,
                `overridden_custom_product_settings` JSON DEFAULT NULL,
                `variations` JSON DEFAULT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),

                CONSTRAINT `fk.myfav_verein.myfav_verein_id`
                    FOREIGN KEY (`myfav_verein_id`)
                    REFERENCES `myfav_verein` (`id`)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
                ,

                CONSTRAINT `fk.myfav_craft_import_article.myfav_craft_import_article_id`
                    FOREIGN KEY (`myfav_craft_import_article_id`)
                    REFERENCES `myfav_craft_import_article` (`id`)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}