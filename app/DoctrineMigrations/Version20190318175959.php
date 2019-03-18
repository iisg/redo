<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add indexes to database to speed up queries.
 */
class Version20190318175959 extends RepekaMigration {
    public function migrate() {
        $this->addSql('CREATE INDEX resource_metadata_reproductor_idx ON resource USING gin ((contents->\'-4\'))');
        $this->addSql('CREATE INDEX resource_metadata_visibility_idx ON resource USING gin ((contents->\'-6\'))');
        $this->addSql('CREATE INDEX resource_metadata_teaser_visibility_idx ON resource USING gin ((contents->\'-7\'))');
    }
}
