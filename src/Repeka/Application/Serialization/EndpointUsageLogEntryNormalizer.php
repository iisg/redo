<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\EndpointUsageLogEntry;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class EndpointUsageLogEntryNormalizer extends AbstractNormalizer implements NormalizationAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $entry EndpointUsageLogEntry
     * @inheritdoc
     */
    public function normalize($entry, $format = null, array $context = []) {
        $normalized = [
            'id' => $entry->getId(),
            'url' => $entry->getUrl(),
            'clientIp' => $entry->getClientIp(),
            'usageDateTime' => $entry->getUsageDateTime(),
            'usageKey' => $entry->getUsageKey(),
            'resourceId' => $entry->getResource()->getId(),
        ];
        return $normalized;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof EndpointUsageLogEntry;
    }
}
