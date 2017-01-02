<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add User#staticPermissions column.
 */
class Version20170102081632 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "user" ADD static_permissions JSON DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".static_permissions IS \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "user" DROP static_permissions');
    }
}
