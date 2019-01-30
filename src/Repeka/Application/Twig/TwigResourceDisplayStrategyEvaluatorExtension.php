<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Repeka\Domain\Utils\PrintableArray;

/**
 * All Twig extensions required for resource & metadata fetching in resource display strategies.
 */
class TwigResourceDisplayStrategyEvaluatorExtension extends \Twig_Extension {
    const USED_METADATA_COLLECTOR_KEY = '__usedMetadataCollector';

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceRepository $resourceRepository, MetadataRepository $metadataRepository) {
        $this->resourceRepository = $resourceRepository;
        $this->metadataRepository = $metadataRepository;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('r', [$this, 'fetchResources']),
            new \Twig_Function('resource', [$this, 'fetchResources']),
            new \Twig_Function('m', [$this, 'fetchMetadataId']),
            new \Twig_Function('metadata', [$this, 'fetchMetadataByNameOrId']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('m', [$this, 'getMetadataValues'], ['needs_context' => true]),
            new \Twig_Filter('metadata', [$this, 'getMetadataValues'], ['needs_context' => true]),
            new \Twig_Filter('metadata*', [$this, 'getMetadataValuesDynamic'], ['needs_context' => true]),
            new \Twig_Filter('m*', [$this, 'getMetadataValuesDynamic'], ['needs_context' => true]),
            new \Twig_Filter('submetadata', [$this, 'getSubmetadataValues']),
            new \Twig_Filter('sub', [$this, 'getSubmetadataValues']),
            new \Twig_Filter('submetadata*', [$this, 'getSubmetadataValuesDynamic']),
            new \Twig_Filter('sub*', [$this, 'getSubmetadataValuesDynamic']),
            new \Twig_Filter('r', [$this, 'fetchResources']),
            new \Twig_Filter('resource', [$this, 'fetchResources']),
        ];
    }

    public function fetchResources($ids) {
        $iterableGiven = is_iterable($ids);
        if (!$iterableGiven) {
            $ids = [$ids];
        }
        $resources = [];
        foreach ($ids as $id) {
            try {
                if ($id instanceof MetadataValue) {
                    $id = $id->getValue();
                }
                if (is_numeric($id)) {
                    $resources[] = $this->resourceRepository->findOne($id);
                } else {
                    throw new \Twig_Error('Given resource ID is not valid.');
                }
            } catch (EntityNotFoundException $e) {
                $resources[] = ResourceContents::empty();
            }
        }
        return $iterableGiven ? $resources : $resources[0];
    }

    public function fetchMetadataId($metadataId, $context = null) {
        if ($metadataId === null) {
            throw new \Twig_Error('Please specify metadata by choosing one of the following syntax: m1, mName, m(1), m("Name")');
        }
        $metadata = $this->fetchMetadataByNameOrId($metadataId, $context);
        return $metadata ? $metadata->getId() : 0;
    }

    public function fetchMetadataByNameOrId($name, $context = null) {
        try {
            $resourceClass = null;
            if ($context instanceof HasResourceClass) {
                $resourceClass = $context->getResourceClass();
            }
            return $this->metadataRepository->findByNameOrId($name, $resourceClass);
        } catch (EntityNotFoundException $e) {
            if ($context) { // maybe the metadata is classless?
                return $this->fetchMetadataByNameOrId($name);
            } else {
                return null;
            }
        }
    }

    public function getMetadataValues(array $twigContext, $contents, $metadataId = null) {
        $metadataId = $this->fetchMetadataId($metadataId, $contents);
        $iterableGiven = is_iterable($contents) && !$contents instanceof ResourceContents;
        if (!$iterableGiven) {
            $contents = [$contents];
        } elseif ($contents instanceof PrintableArray) {
            $contents = $contents->flatten();
        }
        $values = [];
        foreach ($contents as $resource) {
            if ($resource instanceof MetadataValue) {
                $resource = $resource->getValue();
            }
            if (is_numeric($resource)) {
                $resource = $this->fetchResources($resource);
            }
            $values[] = new PrintableArray($resource ? $resource->getValues($metadataId) : []);
            $this->collectUsedMetadata($twigContext, $metadataId, $resource);
        }
        return $iterableGiven ? new PrintableArray($values) : $values[0];
    }

    public function getMetadataValuesDynamic(array $twigContext, $metadataId, $contents) {
        return $this->getMetadataValues($twigContext, $contents, $metadataId);
    }

    public function getSubmetadataValues($metadataValues, $submetadataId = null) {
        $submetadataId = $this->fetchMetadataId($submetadataId);
        $iterableGiven = is_iterable($metadataValues);
        if (!$iterableGiven) {
            $metadataValues = [$metadataValues];
        }
        $values = [];
        foreach ($metadataValues as $metadataValue) {
            $values[] = new PrintableArray($metadataValue->getSubmetadata($submetadataId));
        }
        return $iterableGiven ? new PrintableArray($values) : $values[0];
    }

    public function getSubmetadataValuesDynamic($metadataId, $contents) {
        return $this->getSubmetadataValues($contents, $metadataId);
    }

    private function collectUsedMetadata(array $twigContext, int $metadataId, $resource): void {
        /** @var ResourceDisplayStrategyUsedMetadataCollector $collector */
        if ($collector = ($twigContext[self::USED_METADATA_COLLECTOR_KEY] ?? null)) {
            $collector->addUsedMetadata($metadataId, $resource);
        }
    }
}
