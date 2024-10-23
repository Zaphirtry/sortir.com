<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023124105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campus CHANGE date_modified date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE etat CHANGE date_modified date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE lieu CHANGE date_modified date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE sortie CHANGE duree duree INT NOT NULL, CHANGE nombre_inscrits nombre_inscrits INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ville CHANGE date_modified date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campus CHANGE date_modified date_modified DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE etat CHANGE date_modified date_modified DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE sortie CHANGE duree duree VARCHAR(255) NOT NULL COMMENT \'(DC2Type:dateinterval)\', CHANGE nombre_inscrits nombre_inscrits INT NOT NULL');
        $this->addSql('ALTER TABLE lieu CHANGE date_modified date_modified DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ville CHANGE date_modified date_modified DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
