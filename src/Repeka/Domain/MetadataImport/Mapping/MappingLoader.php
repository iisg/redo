<?php
namespace Repeka\Domain\MetadataImport\Mapping;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQueryBuilder;
use Respect\Validation\Validator;

class MappingLoader {

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param array[] $mappings with ID or name string keys
     */
    public function load(array $mappings, ResourceKind $resourceKind): MappingLoaderResult {
        /** @var Metadata[] $loaded */
        $loaded = [];
        /** @var string[] $missingFromResourceKind */
        $missingFromResourceKind = [];
        foreach ($mappings as $key => $params) {
            $result = $this->loadMapping($key, $params, $resourceKind, $missingFromResourceKind);
            if ($result !== null) {
                $loaded[] = $result;
            } else {
                $missingFromResourceKind[] = $key;
            }
        }
        return new MappingLoaderResult($loaded, $missingFromResourceKind);
    }

    private function loadMapping(
        string $key,
        array $params,
        ResourceKind $resourceKind,
        &$missingFromResourceKind,
        $parent = null
    ): ?Mapping {

        if ($parent) {
            $this->validateSubmetadataParams($params);
            $metadata = $this->findSubmetadata($key, $parent);
        } else {
            $this->validateParams($params);
            $metadata = $this->findMetadataForKey($key, $resourceKind);
        }
        if ($metadata === null) {
            return null;
        }
        $subMetadataMappings = [];
        if (isset($params['submetadata'])) {
            foreach ($params['submetadata'] as $subKey => $subParams) {
                $submetadataMapping = $this->loadMapping($subKey, $subParams, $resourceKind, $missingFromResourceKind, $metadata);
                if ($submetadataMapping) {
                    $subMetadataMappings[] = $submetadataMapping;
                } else {
                    $missingFromResourceKind[] = $subKey;
                }
            }
        }
        return new Mapping($metadata, $params['key'] ?? null, $params['transforms'] ?? [], $subMetadataMappings);
    }

    /**
     * @param string $key
     * @return null|Metadata
     */
    private function findMetadataForKey(string $key, ResourceKind $resourceKind): ?Metadata {
        return $this->findMetadataById($key, $resourceKind) ?: $this->findMetadataByName($key, $resourceKind);
    }

    private function findMetadataById(string $idString, ResourceKind $resourceKind): ?Metadata {
        if (!is_numeric($idString)) {
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

    private function findSubmetadata(string $name, Metadata $parent): ?Metadata {
        try {
            $metadata = $this->metadataRepository->findByName($name, $parent->getResourceClass());
            if ($metadata->getParentId() !== $parent->getId()) {
                return null;
            }
            return $metadata;
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    private function validateParams(array $params): void {
        Validator::keySet(
            Validator::key('key', Validator::notBlank()),
            Validator::key('transforms', Validator::arrayType()->each(Validator::key('name')), false),
            Validator::key('submetadata', Validator::arrayType(), false)
        )->assert($params);
    }

    private function validateSubmetadataParams(array $params): void {
        Validator::keySet(
            Validator::key('transforms', Validator::arrayType()->each(Validator::key('name'))),
            Validator::key('submetadata', Validator::arrayType(), false)
        )->assert($params);
    }
}
