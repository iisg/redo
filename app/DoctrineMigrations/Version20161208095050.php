<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add resource kinds.
 */
class Version20161208095050 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SEQUENCE resource_kind_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE resource_kind (id INT NOT NULL, label JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE metadata ADD resource_kind_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metadata ADD base_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metadata ALTER control DROP NOT NULL');
        $this->addSql('ALTER TABLE metadata ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT FK_4F1434146DC0CB41 FOREIGN KEY (resource_kind_id) REFERENCES resource_kind (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE metadata ADD CONSTRAINT FK_4F1434146967DF41 FOREIGN KEY (base_id) REFERENCES metadata (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4F1434146DC0CB41 ON metadata (resource_kind_id)');
        $this->addSql('CREATE INDEX IDX_4F1434146967DF41 ON metadata (base_id)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE metadata DROP CONSTRAINT FK_4F1434146DC0CB41');
        $this->addSql('DROP SEQUENCE resource_kind_id_seq CASCADE');
        $this->addSql('DROP TABLE resource_kind');
        $this->addSql('ALTER TABLE metadata DROP CONSTRAINT FK_4F1434146967DF41');
        $this->addSql('DROP INDEX IDX_4F1434146DC0CB41');
        $this->addSql('DROP INDEX IDX_4F1434146967DF41');
        $this->addSql('ALTER TABLE metadata DROP resource_kind_id');
        $this->addSql('ALTER TABLE metadata DROP base_id');
        $this->addSql('ALTER TABLE metadata ALTER control SET NOT NULL');
        $this->addSql('ALTER TABLE metadata ALTER name SET NOT NULL');
    }
}
