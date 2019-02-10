<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Utils\StringUtils;

/**
 * Unify metadata names.
 */
class Version20180618111703 extends RepekaMigration {
    public function migrate() {
        $metadataList = $this->fetchAll('SELECT id, resource_class, "name" FROM metadata WHERE "name" IS NOT NULL');
        $occupiedNames = [];
        foreach ($metadataList as $metadata) {
            $unifiedName = StringUtils::normalizeEntityName($metadata['name']);
            $resourceClass = $metadata['resource_class'];
            if (!isset($occupiedNames[$resourceClass])) {
                $occupiedNames[$resourceClass] = [];
            }
            $targetName = $unifiedName;
            $targetNameIndex = 1;
            while (in_array($targetName, $occupiedNames[$resourceClass])) {
                $targetName = $unifiedName . $targetNameIndex++;
            }
            $occupiedNames[$resourceClass][] = $targetName;
            $metadata['name'] = $targetName;
            $this->addSql('UPDATE metadata SET "name"=:name WHERE id=:id', $metadata);
        }
    }
}
