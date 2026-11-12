<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021072853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, deleted_at DATE DEFAULT NULL, user_id INT NOT NULL, anime_id INT NOT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C6794BBE89 (anime_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tracker (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, episodes_watched INT NOT NULL, updated_at DATE NOT NULL, user_id INT NOT NULL, anime_id INT NOT NULL, INDEX IDX_AC632AAFA76ED395 (user_id), INDEX IDX_AC632AAF794BBE89 (anime_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id)');
        $this->addSql('ALTER TABLE tracker ADD CONSTRAINT FK_AC632AAFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tracker ADD CONSTRAINT FK_AC632AAF794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6794BBE89');
        $this->addSql('ALTER TABLE tracker DROP FOREIGN KEY FK_AC632AAFA76ED395');
        $this->addSql('ALTER TABLE tracker DROP FOREIGN KEY FK_AC632AAF794BBE89');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE tracker');
    }
}
