<?php declare(strict_types=1);
namespace Repeka\Migrations;

/** Adds default flexible mode constraint value to metadata with flexible date or date range control. */
class Version20190704070322 extends RepekaMigration {

    public function migrate() {
        $flexibleMode = json_encode(['flexibleDateMode' => 'flexible']);
        $this->addSql(
            "UPDATE metadata SET constraints = constraints || '$flexibleMode'::jsonb 
             WHERE control IN ('flexible-date', 'date-range') AND constraints->'flexibleDateMode' IS NULL"
        );
    }
}
