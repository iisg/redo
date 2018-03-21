<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Audit.
 */
class Version20180321122132 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SEQUENCE audit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE audit (id INT NOT NULL, user_id INT DEFAULT NULL, commandName VARCHAR(64) NOT NULL, data jsonb NOT NULL, successful BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9218FF79A76ED395 ON audit (user_id)');
        $this->addSql('CREATE INDEX audit_entry_type_idx ON audit (commandName)');
        $this->addSql('COMMENT ON COLUMN audit.data IS \'(DC2Type:jsonb)\'');
        $this->addSql('COMMENT ON COLUMN audit.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE audit ADD CONSTRAINT FK_9218FF79A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE audit_id_seq CASCADE');
        $this->addSql('DROP TABLE audit');
    }
}
