<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\PrintableArray;

class TwigResourceDisplayStrategyEvaluatorExtension extends \Twig_Extension {
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
            new \Twig_Function('m', [$this, 'fetchMetadataIdByName']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('m', [$this, 'getMetadataValues']),
            new \Twig_Filter('metadata', [$this, 'getMetadataValues']),
            new \Twig_Filter('metadata*', [$this, 'getMetadataValuesDynamic']),
            new \Twig_Filter('m*', [$this, 'getMetadataValuesDynamic']),
            new \Twig_Filter('submetadata', [$this, 'getSubmetadataValues']),
            new \Twig_Filter('sub', [$this, 'getSubmetadataValues']),
            new \Twig_Filter('submetadata*', [$this, 'getSubmetadataValuesDynamic']),
            new \Twig_Filter('sub*', [$this, 'getSubmetadataValuesDynamic']),
            new \Twig_Filter('r', [$this, 'fetchResources']),
            new \Twig_Filter('resource', [$this, 'fetchResources']),
            new \Twig_Filter('sum', [$this, 'sumIterable']),
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

    public function fetchMetadataIdByName(string $name, $context = null) {
        try {
            $resourceClass = null;
            if ($context instanceof HasResourceClass) {
                $resourceClass = $context->getResourceClass();
            }
            $metadata = $this->metadataRepository->findByName($name, $resourceClass);
            return $metadata->getId();
        } catch (EntityNotFoundException $e) {
            return 0;
        }
    }

    public function getMetadataValues($contents, $metadataId = null) {
        if ($metadataId === null) {
            throw new \Twig_Error('Please specify metadata by choosing one of the following syntax: m1, mName, m(1), m("Name")');
        }
        if (!is_numeric($metadataId)) {
            $metadataId = $this->fetchMetadataIdByName($metadataId, $contents);
        }
        $iterableGiven = is_iterable($contents) && !$contents instanceof ResourceContents;
        if (!$iterableGiven) {
            $contents = [$contents];
        }
        $values = [];
        foreach ($contents as $resource) {
            if ($resource instanceof MetadataValue) {
                $resource = $resource->getValue();
            }
            if (is_numeric($resource)) {
                $resource = $this->fetchResources($resource);
            }
            $values[] = new PrintableArray($resource->getValues($metadataId));
        }
        return $iterableGiven ? new PrintableArray($values) : $values[0];
    }

    public function getMetadataValuesDynamic($metadataId, $contents) {
        return $this->getMetadataValues($contents, $metadataId);
    }

    public function getSubmetadataValues($metadataValues, $submetadataId = null) {
        if ($submetadataId === null) {
            throw new \Twig_Error(
                'Please specify metadata by choosing one of the following syntax: '
                . 'submetadata1, submetadataName, submetadata(1), submetadata("Name")'
            );
        }
        if (!is_numeric($submetadataId)) {
            $submetadataId = $this->fetchMetadataIdByName($submetadataId);
        }
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

    public function sumIterable($iterable) {
        if ($iterable instanceof PrintableArray) {
            $iterable = $iterable->flatten();
        }
        if (!is_array($iterable)) {
            $iterable = iterator_to_array($iterable);
        }
        $iterable = array_map(
            function ($value) {
                return is_numeric($value) ? $value : strval($value);
            },
            $iterable
        );
        return array_sum($iterable);
    }
}
