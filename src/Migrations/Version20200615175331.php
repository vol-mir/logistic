<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200615175331 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods DROP FOREIGN KEY FK_BCEF828A65E1FE6C');
        $this->addSql('ALTER TABLE tasks_goods ADD CONSTRAINT FK_BCEF828A65E1FE6C FOREIGN KEY (address_office_id) REFERENCES addresses (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods DROP FOREIGN KEY FK_BCEF828A65E1FE6C');
        $this->addSql('ALTER TABLE tasks_goods ADD CONSTRAINT FK_BCEF828A65E1FE6C FOREIGN KEY (address_office_id) REFERENCES addresses (id)');
    }
}
