<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200521132721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE transports (id INT AUTO_INCREMENT NOT NULL, marka VARCHAR(190) NOT NULL, model VARCHAR(190) NOT NULL, number VARCHAR(190) NOT NULL, kind INT UNSIGNED DEFAULT 1 NOT NULL, carrying DOUBLE PRECISION DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE drivers CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE first_name first_name VARCHAR(190) DEFAULT NULL, CHANGE last_name last_name VARCHAR(190) DEFAULT NULL, CHANGE middle_name middle_name VARCHAR(190) DEFAULT NULL, CHANGE phone phone VARCHAR(190) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE transports');
        $this->addSql('ALTER TABLE drivers CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE first_name first_name VARCHAR(191) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE last_name last_name VARCHAR(191) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE middle_name middle_name VARCHAR(191) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE phone phone VARCHAR(191) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
