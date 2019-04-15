<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add resourceKind.allowedToClone field
 */
class Version20190415153604 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE resource_kind ADD allowed_to_clone BOOLEAN DEFAULT false');
    }
}
