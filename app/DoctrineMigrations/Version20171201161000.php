<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;

/**
 * Display strategies.
 */
class Version20171201161000 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("ALTER TABLE resource_kind ADD display_strategies JSONB NOT NULL DEFAULT '{}'::JSONB");
        $usernameDisplayStrategy = '{{m' . SystemMetadata::USERNAME . '}}';
        $this->addSql("UPDATE resource_kind SET display_strategies='{\"header\": \"$usernameDisplayStrategy\", \"dropdown\": \"$usernameDisplayStrategy\"}'::JSONB WHERE id=" . SystemResourceKind::USER);
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "resource_kind" DROP display_strategies');
    }
}
