<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the pseudo column to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD pseudo VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE user SET pseudo = CONCAT('user_', id) WHERE pseudo IS NULL");
        $this->addSql('ALTER TABLE user CHANGE pseudo pseudo VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP pseudo');
    }
}
