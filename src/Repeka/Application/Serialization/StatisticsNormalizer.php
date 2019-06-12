<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\UseCase\Stats\StatisticEntry;
use Repeka\Domain\UseCase\Stats\Statistics;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class StatisticsNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $statistics Statistics
     * @inheritdoc
     */
    public function normalize($statistics, $format = null, array $context = []) {
        return [
            'usageKey' => $statistics->getUsageKey(),
            'statisticsEntries' => array_map(
                function (StatisticEntry $entry) use ($format, $context) {
                    return $this->normalizer->normalize($entry, $format, $context);
                },
                $statistics->getStatisticsEntries()
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Statistics;
    }
}
