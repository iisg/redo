<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Erase every maxCount=0 constraint.
 */
class Version20180424170831 extends RepekaMigration {
    public function migrate() {
        $this->addSql("UPDATE metadata SET constraints = constraints - 'maxCount' where constraints -> 'maxCount' = '0';");
    }
}
