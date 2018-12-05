<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\EntityUtils;

/**
 * Migrate file paths in file metadata.
 */
class Version20181213092357 extends RepekaMigration {
    public function migrate() {
        $fileMetadataIds = EntityUtils::mapToIds(
            $this->fetchAll('SELECT id FROM metadata WHERE control=:control', ['control' => MetadataControl::FILE])
        );
        $resources = $this->fetchAll('SELECT id, contents FROM resource');
        /** @var ResourceEntity $resource */
        foreach ($resources as $resource) {
            $resource['contents'] = json_decode($resource['contents'], true);
            $resource['contents'] = ResourceContents::fromArray($resource['contents']);
            $changed = false;
            $resource['contents'] = $resource['contents']->mapAllValues(
                function (MetadataValue $value, int $metadataId) use ($fileMetadataIds, &$changed) {
                    if (in_array($metadataId, $fileMetadataIds)) {
                        $changed = true;
                        $newFilePath = 'resourceFiles/' . basename($value->getValue());
                        return $value->withNewValue($newFilePath);
                    }
                    return $value;
                }
            );
            if ($changed) {
                $resource['contents'] = json_encode($resource['contents']);
                $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource);
            }
        }
    }
}
