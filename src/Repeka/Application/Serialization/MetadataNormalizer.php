<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class MetadataNormalizer extends AbstractNormalizer {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository, MetadataRepository $metadataRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
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
            'groupId' => $metadata->getGroupId(),
            'displayStrategy' => $metadata->getDisplayStrategy(),
            'shownInBrief' => $metadata->isShownInBrief(),
            'copyToChildResource' => $metadata->isCopiedToChildResource(),
            'resourceClass' => $metadata->getResourceClass(),
            'canDetermineAssignees' => $metadata->canDetermineAssignees($this->resourceKindRepository),
            'hasChildren' => $this->metadataRepository->countByParent($metadata) > 0,
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Metadata;
    }
}
