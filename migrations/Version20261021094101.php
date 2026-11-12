<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261021094101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anime_genre (anime_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_EFF953C7794BBE89 (anime_id), INDEX IDX_EFF953C74296D31F (genre_id), PRIMARY KEY (anime_id, genre_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE anime_genre ADD CONSTRAINT FK_EFF953C7794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anime_genre ADD CONSTRAINT FK_EFF953C74296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY `FK_13045942446F285F`');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY `FK_130459423256915B`');
        $this->addSql('DROP INDEX IDX_130459423256915B ON anime');
        $this->addSql('ALTER TABLE anime CHANGE relation_id created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942446F285F FOREIGN KEY (studio_id) REFERENCES studio (id)');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_13045942B03A8386 ON anime (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime_genre DROP FOREIGN KEY FK_EFF953C7794BBE89');
        $this->addSql('ALTER TABLE anime_genre DROP FOREIGN KEY FK_EFF953C74296D31F');
        $this->addSql('DROP TABLE anime_genre');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_13045942446F285F');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_13045942B03A8386');
        $this->addSql('DROP INDEX IDX_13045942B03A8386 ON anime');
        $this->addSql('ALTER TABLE anime CHANGE created_by_id relation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT `FK_13045942446F285F` FOREIGN KEY (studio_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT `FK_130459423256915B` FOREIGN KEY (relation_id) REFERENCES studio (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_130459423256915B ON anime (relation_id)');
    }
}
