<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ResourceKindNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $resourceKind ResourceKind
     * @inheritdoc
     */
    public function normalize($resourceKind, $format = null, array $context = []) {
        return [
            'id' => $resourceKind->getId(),
            'name' => $resourceKind->getName(),
            'label' => $this->emptyArrayAsObject($resourceKind->getLabel()),
            'metadataList' => array_map(
                function (Metadata $metadata) use ($format, $context) {
                    return $this->normalizer->normalize($metadata, $format, $context);
                },
                $resourceKind->getMetadataList()
            ),
            'workflowId' => $resourceKind->getWorkflow() ? $resourceKind->getWorkflow()->getId() : null,
            'resourceClass' => $resourceKind->getResourceClass(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceKind;
    }
}
