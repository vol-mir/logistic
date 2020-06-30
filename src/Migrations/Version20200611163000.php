<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611163000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods ADD address_office_id INT DEFAULT NULL, ADD address_goods_yard_id INT NOT NULL');
        $this->addSql('ALTER TABLE tasks_goods ADD CONSTRAINT FK_BCEF828A65E1FE6C FOREIGN KEY (address_office_id) REFERENCES addresses (id)');
        $this->addSql('ALTER TABLE tasks_goods ADD CONSTRAINT FK_BCEF828AE65D5F95 FOREIGN KEY (address_goods_yard_id) REFERENCES addresses (id)');
        $this->addSql('CREATE INDEX IDX_BCEF828A65E1FE6C ON tasks_goods (address_office_id)');
        $this->addSql('CREATE INDEX IDX_BCEF828AE65D5F95 ON tasks_goods (address_goods_yard_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tasks_goods DROP FOREIGN KEY FK_BCEF828A65E1FE6C');
        $this->addSql('ALTER TABLE tasks_goods DROP FOREIGN KEY FK_BCEF828AE65D5F95');
        $this->addSql('DROP INDEX IDX_BCEF828A65E1FE6C ON tasks_goods');
        $this->addSql('DROP INDEX IDX_BCEF828AE65D5F95 ON tasks_goods');
        $this->addSql('ALTER TABLE tasks_goods DROP address_office_id, DROP address_goods_yard_id');
    }
}
