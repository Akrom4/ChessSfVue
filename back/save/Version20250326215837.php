<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326215837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop themes column from puzzle table';
    }

    public function up(Schema $schema): void
    {
        // Check if column exists before dropping
        $this->addSql('SET @dbname = DATABASE()');
        $this->addSql('SET @tablename = "puzzle"');
        $this->addSql('SET @columnname = "themes"');
        $this->addSql('SET @query = CONCAT("ALTER TABLE ", @tablename, " DROP COLUMN ", @columnname)');
        $this->addSql('SET @exists := (
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = @dbname
            AND table_name = @tablename
            AND column_name = @columnname
        )');
        $this->addSql('SET @query = IF(@exists > 0, @query, "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @query');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }

    public function down(Schema $schema): void
    {
        // Check if column doesn't exist before adding
        $this->addSql('SET @dbname = DATABASE()');
        $this->addSql('SET @tablename = "puzzle"');
        $this->addSql('SET @columnname = "themes"');
        $this->addSql('SET @query = CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(255) NOT NULL")');
        $this->addSql('SET @exists := (
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = @dbname
            AND table_name = @tablename
            AND column_name = @columnname
        )');
        $this->addSql('SET @query = IF(@exists = 0, @query, "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @query');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }
}
