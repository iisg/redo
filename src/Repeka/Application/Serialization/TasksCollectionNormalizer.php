<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Assignment\TasksCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class TasksCollectionNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $tasksCollection TasksCollection
     * @inheritdoc
     */
    public function normalize($tasksCollection, $format = null, array $context = []) {
        return [
            'resourceClass' => $tasksCollection->getResourceClass(),
            'myTasks' => array_map(
                function (ResourceEntity $resource) use ($format, $context) {
                    return $this->normalizer->normalize($resource, $format, $context);
                },
                $tasksCollection->getMyTasks()
            ),
            'possibleTasks' => array_map(
                function (ResourceEntity $resource) use ($format, $context) {
                    return $this->normalizer->normalize($resource, $format, $context);
                },
                $tasksCollection->getPossibleTasks()
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof TasksCollection;
    }
}
