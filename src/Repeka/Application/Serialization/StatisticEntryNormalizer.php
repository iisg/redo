<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\UseCase\Stats\StatisticEntry;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class StatisticEntryNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $statisticEntry StatisticEntry
     * @inheritdoc
     */
    public function normalize($statisticEntry, $format = null, array $context = []) {
        return [
            'usageKey' => $statisticEntry->getUsageKey(),
            'clientIp' => $statisticEntry->getClientIp(),
            'statMonth' => $statisticEntry->getStatMonth(),
            'monthlySum' => $statisticEntry->getMonthlySum(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof StatisticEntry;
    }
}
