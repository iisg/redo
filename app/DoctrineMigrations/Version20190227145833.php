<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add indexes to database to speed up queries.
 */
class Version20190227145833 extends RepekaMigration {
    public function migrate() {
        $this->addSql('CREATE INDEX audit_entry_created_at_idx ON audit (created_at)');
        $this->addSql('CREATE INDEX resource_metadata_parent_idx ON resource USING gin ((contents->\'-1\'))');
    }
}
