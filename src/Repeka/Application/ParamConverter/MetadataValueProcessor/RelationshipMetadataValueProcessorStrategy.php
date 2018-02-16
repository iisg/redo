<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\ResourceRepository;
use Symfony\Component\HttpFoundation\Request;

class RelationshipMetadataValueProcessorStrategy implements MetadataValueProcessorStrategy {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function processValue($relatedResourceId, Request $request) {
        return $this->resourceRepository->findOne($relatedResourceId);
    }

    public function getSupportedControl(): MetadataControl {
        return MetadataControl::RELATIONSHIP();
    }
}
