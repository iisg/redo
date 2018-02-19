<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;

/**
 * Set shownInBrief=false for parent metadata.
 */
class Version20180219213547 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('UPDATE metadata SET shown_in_brief=false WHERE id=' . SystemMetadata::PARENT);
    }

    public function down(Schema $schema) {
    }
}
