<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170307101932 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE resource_kind ADD workflow_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_kind ADD CONSTRAINT FK_34E41C632C7C2CBA FOREIGN KEY (workflow_id) REFERENCES workflow (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_34E41C632C7C2CBA ON resource_kind (workflow_id)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE resource_kind DROP CONSTRAINT FK_34E41C632C7C2CBA');
        $this->addSql('DROP INDEX IDX_34E41C632C7C2CBA');
        $this->addSql('ALTER TABLE resource_kind DROP workflow_id');
    }
}
