<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Fix missing resource class for submetadata with parents.
 */
class Version20180216235421 extends RepekaMigration {
    public function migrate() {
        $this->addSql(
            'UPDATE metadata m1 SET resource_class = (SELECT resource_class FROM metadata m2 WHERE m2.id = m1.parent_id) WHERE parent_id IS NOT NULL'
        );
    }
}
