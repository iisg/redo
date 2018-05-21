<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Clear invalid resource markings. For some reason, the 0.5.0 version sometimes wrote 'null' string into marking column. It caused errors
 * when filtering resources by places.
 */
class Version20180428200114 extends RepekaMigration {
    public function migrate() {
        $this->addSql('UPDATE resource SET marking = NULL WHERE jsonb_typeof(marking) <> \'object\';');
    }
}
