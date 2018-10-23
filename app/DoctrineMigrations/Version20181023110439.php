<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add display_strategies_dirty to the resource.
 */
class Version20181023110439 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE resource ADD display_strategies_dirty BOOLEAN DEFAULT \'true\' NOT NULL');
    }
}
