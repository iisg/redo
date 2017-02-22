<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add metadata parent ID
 */
class Version20170222184559 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE metadata ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT FK_4F143414727ACA70 FOREIGN KEY (parent_id) REFERENCES metadata (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4F143414727ACA70 ON metadata (parent_id)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE metadata DROP CONSTRAINT FK_4F143414727ACA70');
        $this->addSql('DROP INDEX IDX_4F143414727ACA70');
        $this->addSql('ALTER TABLE metadata DROP parent_id');
    }
}
