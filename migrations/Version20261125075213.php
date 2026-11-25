<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20261125075213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY `FK_13045942446F285F`');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY `FK_13045942B03A8386`');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942446F285F FOREIGN KEY (studio_id) REFERENCES studio (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_13045942446F285F');
        $this->addSql('ALTER TABLE anime DROP FOREIGN KEY FK_13045942B03A8386');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT `FK_13045942446F285F` FOREIGN KEY (studio_id) REFERENCES studio (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT `FK_13045942B03A8386` FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
