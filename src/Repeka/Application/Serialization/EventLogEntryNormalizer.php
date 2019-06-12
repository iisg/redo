<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\EventLogEntry;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class EventLogEntryNormalizer extends AbstractNormalizer implements NormalizationAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $entry EventLogEntry
     * @inheritdoc
     */
    public function normalize($entry, $format = null, array $context = []) {
        $normalized = [
            'id' => $entry->getId(),
            'url' => $entry->getUrl(),
            'clientIp' => $entry->getClientIp(),
            'usageDateTime' => $entry->getEventDateTime(),
            'usageKey' => $entry->getEventName(),
            'resourceId' => $entry->getResource()->getId(),
        ];
        return $normalized;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof EventLogEntry;
    }
}
