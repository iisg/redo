<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Synchronize migrations with schema after adding new JSONB library support.
 */
class Version20180215094822 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE user_role ALTER role_id SET NOT NULL');
        $this->addSql('ALTER TABLE user_role ADD PRIMARY KEY (user_id, role_id)');
        $this->addSql('ALTER TABLE role ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE metadata ALTER description SET DEFAULT \'{}\'');
        $this->addSql('ALTER TABLE metadata ALTER placeholder SET DEFAULT \'{}\'');
        $this->addSql('ALTER TABLE metadata ALTER constraints DROP DEFAULT');
        $this->addSql('ALTER TABLE resource_kind ALTER display_strategies DROP DEFAULT');
        $this->addSql('ALTER TABLE resource_kind ALTER metadata_list SET NOT NULL');
        // remove "DC2Type:json" comments that override the column type for Doctrine added in Version20170210084530
        // @see https://stackoverflow.com/a/43474146/878514
        $this->addSql('COMMENT ON COLUMN workflow.places IS NULL');
        $this->addSql('COMMENT ON COLUMN workflow.transitions IS NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE resource_kind ALTER metadata_list DROP NOT NULL');
        $this->addSql('ALTER TABLE resource_kind ALTER display_strategies SET DEFAULT \'{}\'');
        $this->addSql('ALTER TABLE metadata ALTER description DROP DEFAULT');
        $this->addSql('ALTER TABLE metadata ALTER placeholder DROP DEFAULT');
        $this->addSql('ALTER TABLE metadata ALTER constraints SET DEFAULT \'{}\'');
        $this->addSql('CREATE SEQUENCE role_id_seq');
        $this->addSql('SELECT setval(\'role_id_seq\', (SELECT MAX(id) FROM role))');
        $this->addSql('ALTER TABLE role ALTER id SET DEFAULT nextval(\'role_id_seq\')');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE user_role ALTER role_id DROP NOT NULL');
    }
}
