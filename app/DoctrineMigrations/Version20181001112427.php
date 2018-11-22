<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\Metadata;

/**
 * Add group to metadata
 */
class Version20181001112427 extends RepekaMigration {
    public function migrate() {
        $defaultGroup = Metadata::DEFAULT_GROUP;
        $this->addSql("ALTER TABLE \"metadata\" ADD COLUMN group_id VARCHAR(64) NOT NULL DEFAULT '$defaultGroup';");
    }
}
