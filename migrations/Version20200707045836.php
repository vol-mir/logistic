<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200707045836 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE task_goods_driver (task_goods_id INT NOT NULL, driver_id INT NOT NULL, INDEX IDX_5A275839F7A8DFD9 (task_goods_id), INDEX IDX_5A275839C3423909 (driver_id), PRIMARY KEY(task_goods_id, driver_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_goods_driver ADD CONSTRAINT FK_5A275839F7A8DFD9 FOREIGN KEY (task_goods_id) REFERENCES tasks_goods (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_goods_driver ADD CONSTRAINT FK_5A275839C3423909 FOREIGN KEY (driver_id) REFERENCES drivers (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE task_goods_driver');
    }
}
