<?php
namespace Repeka\Domain\MetadataImport\Mapping;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Respect\Validation\Validator;

class MappingLoader {
    /**
     * @param array[] $mappings with ID or name string keys
     */
    public function load(array $mappings, ResourceKind $resourceKind): MappingLoaderResult {
        /** @var Metadata[] $loaded */
        $loaded = [];
        /** @var string[] $missingFromResourceKind */
        $missingFromResourceKind = [];
        foreach ($mappings as $key => $params) {
            $result = $this->loadMapping($key, $params, $resourceKind);
            if ($result !== null) {
                $loaded[] = $result;
            } else {
                $missingFromResourceKind[] = $key;
            }
        }
        return new MappingLoaderResult($loaded, $missingFromResourceKind);
    }

    private function loadMapping(string $key, array $params, ResourceKind $resourceKind): ?Mapping {
        $this->validateParams($params);
        $metadata = $this->findMetadataForKey($key, $resourceKind);
        if ($metadata === null) {
            return null;
        }
        return new Mapping($metadata, $params['key'], $params['transforms'] ?? []);
    }

    /**
     * @param string $key
     * @return null|Metadata
     */
    private function findMetadataForKey(string $key, ResourceKind $resourceKind): ?Metadata {
        return $this->findMetadataById($key, $resourceKind) ?: $this->findMetadataByName($key, $resourceKind);
    }

    private function findMetadataById(string $idString, ResourceKind $resourceKind): ?Metadata {
        if (!preg_match('/^\d+$/', $idString)) {
            return null;
        }
        try {
            return $resourceKind->getMetadataById(intval($idString));
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function findMetadataByName(string $name, ResourceKind $resourceKind): ?Metadata {
        try {
            return $resourceKind->getMetadataByName($name);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function validateParams(array $params): void {
        Validator::keySet(
            Validator::key('key', Validator::notBlank()),
            Validator::key('transforms', Validator::arrayType()->each(Validator::key('name')), false)
        )->assert($params);
    }
}
