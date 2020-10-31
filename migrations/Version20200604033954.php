<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604033954 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods ADD organization_id INT NOT NULL');
        $this->addSql('ALTER TABLE tasks_goods ADD CONSTRAINT FK_BCEF828A32C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id)');
        $this->addSql('CREATE INDEX IDX_BCEF828A32C8A3DE ON tasks_goods (organization_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods DROP FOREIGN KEY FK_BCEF828A32C8A3DE');
        $this->addSql('DROP INDEX IDX_BCEF828A32C8A3DE ON tasks_goods');
        $this->addSql('ALTER TABLE tasks_goods DROP organization_id');
    }
}
