<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325023843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE puzzle CHANGE fen fen VARCHAR(100) NOT NULL, CHANGE moves moves VARCHAR(100) NOT NULL, CHANGE rating rating SMALLINT UNSIGNED NOT NULL, CHANGE rating_deviation rating_deviation SMALLINT UNSIGNED NOT NULL, CHANGE popularity popularity SMALLINT UNSIGNED NOT NULL, CHANGE nb_plays nb_plays INT UNSIGNED NOT NULL, CHANGE themes themes VARCHAR(255) NOT NULL, CHANGE opening_tags opening_tags VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX rating_idx ON puzzle (rating)');
        $this->addSql('CREATE INDEX themes_idx ON puzzle (themes)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX rating_idx ON puzzle');
        $this->addSql('DROP INDEX themes_idx ON puzzle');
        $this->addSql('ALTER TABLE puzzle CHANGE fen fen VARCHAR(255) NOT NULL, CHANGE moves moves VARCHAR(255) NOT NULL, CHANGE rating rating INT NOT NULL, CHANGE rating_deviation rating_deviation INT NOT NULL, CHANGE popularity popularity INT NOT NULL, CHANGE nb_plays nb_plays INT NOT NULL, CHANGE themes themes LONGTEXT NOT NULL, CHANGE opening_tags opening_tags LONGTEXT DEFAULT NULL');
    }
}
