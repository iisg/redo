<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Utils\StringUtils;

/**
 * Add name field to resource kind.
 * Name by default has the value of PL label. In case of duplicates suffix is added.
 */
class Version20190210155945 extends RepekaMigration {
    public function migrate() {
        $this->addSql("ALTER TABLE resource_kind ADD name varchar(255) DEFAULT NULL");
        $resourceKinds = $this->fetchAll("SELECT id, label->>'PL' AS label FROM resource_kind");
        $namesCount = [];
        foreach ($resourceKinds as $resourceKind) {
            $name = $this->labelToName($resourceKind['label']);
            if (!array_key_exists($name, $namesCount)) {
                $namesCount[$name] = 1;
            } else {
                $namesCount[$name] += 1;
                $name .= '_' . $namesCount[$name];
            }
            $resourceKind['name'] = $name;
            $this->addSql("UPDATE resource_kind SET name = :name WHERE id = :id", $resourceKind);
        }
        $this->addSql("ALTER TABLE resource_kind ALTER COLUMN name SET NOT NULL");
    }

    private function labelToName(string $label): string {
        $normalized = StringUtils::normalizeEntityName($label);
        if (is_numeric($normalized)) {
            $normalized .= '_';
        }
        return $normalized;
    }
}
