<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change language.code from VARCHAR(5) to VARCHAR(10).
 */
class Version20170123211757 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE language ALTER code TYPE VARCHAR(10)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE language ALTER code TYPE VARCHAR(5)');
    }
}
