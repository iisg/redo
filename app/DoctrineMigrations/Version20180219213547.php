<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;

/**
 * Set shownInBrief=false for parent metadata.
 */
class Version20180219213547 extends RepekaMigration {
    public function migrate() {
        $this->addSql('UPDATE metadata SET shown_in_brief=false WHERE id=' . SystemMetadata::PARENT);
    }
}
