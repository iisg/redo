<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Support longer URLs in Event Log.
 */
class Version20190712113436 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE event_log ALTER url TYPE VARCHAR(2048)');
    }
}
