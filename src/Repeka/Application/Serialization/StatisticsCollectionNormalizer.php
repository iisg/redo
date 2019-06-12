<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\UseCase\Stats\Statistics;
use Repeka\Domain\UseCase\Stats\StatisticsCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class StatisticsCollectionNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $statisticsCollection StatisticsCollection
     * @inheritdoc
     */
    public function normalize($statisticsCollection, $format = null, array $context = []) {
        return [
            'resourcesCount' => $statisticsCollection->getResourcesCount(),
            'openResourcesCount' => $statisticsCollection->getOpenResourcesCount(),
            'statistics' => array_map(
                function (Statistics $statistics) use ($format, $context) {
                    return $this->normalizer->normalize($statistics, $format, $context);
                },
                $statisticsCollection->getStatistics()
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof StatisticsCollection;
    }
}
