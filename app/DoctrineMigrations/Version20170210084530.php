<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add workflow table.
 */
class Version20170210084530 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SEQUENCE workflow_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE workflow (id INT NOT NULL, name JSONB NOT NULL, places JSON DEFAULT \'[]\' NOT NULL, transitions JSON DEFAULT \'[]\' NOT NULL, diagram TEXT DEFAULT NULL, thumbnail TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN workflow.places IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN workflow.transitions IS \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE workflow_id_seq CASCADE');
        $this->addSql('DROP TABLE workflow');
    }
}
