<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021070848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anime (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, synopsis LONGTEXT DEFAULT NULL, release_year INT DEFAULT NULL, episodes_count INT DEFAULT NULL, poster_image VARCHAR(255) DEFAULT NULL, is_featured TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, studio_id INT DEFAULT NULL, relation_id INT DEFAULT NULL, INDEX IDX_13045942446F285F (studio_id), INDEX IDX_130459423256915B (relation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE studio (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942446F285F FOREIGN KEY (studio_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_130459423256915B FOREIGN KEY (relation_id) REFERENCES studio (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_13045942446F285F');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_130459423256915B');
        $this->addSql('DROP TABLE anime');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE studio');
    }
}
