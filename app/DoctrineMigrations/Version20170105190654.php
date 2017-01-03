<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change name and surname to firstname and lastname.
 */
class Version20170105190654 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN name TO firstname');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN surname TO lastname');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN firstname TO name');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN lastname TO surname');
    }
}
