<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326161212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE puzzle_theme (puzzle_id VARCHAR(10) NOT NULL COLLATE `utf8mb4_bin`, theme_id INT NOT NULL, INDEX IDX_A64999B1D9816812 (puzzle_id), INDEX IDX_A64999B159027487 (theme_id), PRIMARY KEY(puzzle_id, theme_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_9775E7085E237E06 (name), INDEX theme_name_idx (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE puzzle_theme ADD CONSTRAINT FK_A64999B1D9816812 FOREIGN KEY (puzzle_id) REFERENCES puzzle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE puzzle_theme ADD CONSTRAINT FK_A64999B159027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX themes_idx ON puzzle');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE puzzle_theme DROP FOREIGN KEY FK_A64999B1D9816812');
        $this->addSql('ALTER TABLE puzzle_theme DROP FOREIGN KEY FK_A64999B159027487');
        $this->addSql('DROP TABLE puzzle_theme');
        $this->addSql('DROP TABLE theme');
        $this->addSql('CREATE INDEX themes_idx ON puzzle (themes)');
    }
}
