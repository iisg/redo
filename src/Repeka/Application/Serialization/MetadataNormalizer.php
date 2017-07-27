<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Metadata;

class MetadataNormalizer extends AbstractNormalizer {
    /**
     * @param $metadata Metadata
     * @inheritdoc
     */
    public function normalize($metadata, $format = null, array $context = []) {
        return [
            'id' => $metadata->getId(),
            'name' => $metadata->getName(),
            'control' => $metadata->getControl(),
            'label' => $metadata->getLabel(),
            'placeholder' => $this->emptyArrayAsObject($metadata->getPlaceholder()),
            'description' => $this->emptyArrayAsObject($metadata->getDescription()),
            'baseId' => $metadata->getBaseId(),
            'parentId' => $metadata->getParentId(),
            'constraints' => $this->emptyArrayAsObject($metadata->getConstraints()),
            'shownInBrief' => $metadata->isShownInBrief(),
            'resourceClass' => $metadata->getResourceClass(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Metadata;
    }
}
