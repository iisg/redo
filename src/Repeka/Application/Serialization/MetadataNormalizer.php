<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\ResourceKindRepository;

class MetadataNormalizer extends AbstractNormalizer {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /**
     * @param $metadata Metadata
     * @inheritdoc
     */
    public function normalize($metadata, $format = null, array $context = []) {
        return [
            'id' => $metadata->getId(),
            'name' => $metadata->getName(),
            'control' => $metadata->getControl()->getValue(),
            'label' => $metadata->getLabel(),
            'placeholder' => $this->emptyArrayAsObject($metadata->getPlaceholder()),
            'description' => $this->emptyArrayAsObject($metadata->getDescription()),
            'baseId' => $metadata->getBaseId(),
            'parentId' => $metadata->getParentId(),
            'constraints' => $this->emptyArrayAsObject($metadata->getConstraints()),
            'shownInBrief' => $metadata->isShownInBrief(),
            'copyToChildResource' => $metadata->isCopiedToChildResource(),
            'resourceClass' => $metadata->getResourceClass(),
            'canDetermineAssignees' => $metadata->canDetermineAssignees($this->resourceKindRepository),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Metadata;
    }
}
