<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200711043423 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE task_goods_transport (task_goods_id INT NOT NULL, transport_id INT NOT NULL, INDEX IDX_3F7A6165F7A8DFD9 (task_goods_id), INDEX IDX_3F7A61659909C13F (transport_id), PRIMARY KEY(task_goods_id, transport_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_goods_transport ADD CONSTRAINT FK_3F7A6165F7A8DFD9 FOREIGN KEY (task_goods_id) REFERENCES tasks_goods (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_goods_transport ADD CONSTRAINT FK_3F7A61659909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE task_goods_transport');
    }
}
