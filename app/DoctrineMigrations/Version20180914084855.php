<?php
namespace Repeka\Migrations;

use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;

/**
 * Migrate date control content to atom format. Migrate date control to timestamp control
 */
class Version20180914084855 extends RepekaMigration {
    public function migrate() {
        $resources = $this->fetchAll('SELECT id, contents FROM resource');
        $metadataList = $this->connection->fetchAll('SELECT id, control FROM metadata');
        $metadataMap = [];
        foreach ($metadataList as $metadata) {
            $metadataMap[$metadata['id']] = $metadata['control'];
        }
        /** @var ResourceEntity $resource */
        foreach ($resources as $resource) {
            $resource['contents'] = json_decode($resource['contents'], true);
            $resource['contents'] = ResourceContents::fromArray($resource['contents']);
            $resource['contents'] = $resource['contents']->mapAllValues(
                function (MetadataValue $value, int $metadataId) use ($metadataMap) {
                    if (array_key_exists($metadataId, $metadataMap) && $metadataMap[$metadataId] === 'date') {
                        return $value->withNewValue(MetadataDateControlConverterUtil::convertDateToAtomFormat($value));
                    }
                    return $value;
                }
            );
            $resource['contents'] = json_encode($resource['contents']);
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource);
        }
        $this->addSql(
            'UPDATE metadata SET control = :tsControl WHERE control = :dateControl',
            ['tsControl' => 'timestamp', 'dateControl' => 'date']
        );
    }
}
