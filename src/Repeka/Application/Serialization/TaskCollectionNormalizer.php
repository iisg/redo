<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\UseCase\Assignment\TaskCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class TaskCollectionNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $taskCollection TaskCollection
     * @inheritdoc
     */
    public function normalize($taskCollection, $format = null, array $context = []) {
        $tasks = $taskCollection->getTasks();
        return [
            'resourceClass' => $taskCollection->getResourceClass(),
            'taskStatus' => $taskCollection->getTaskStatus()->getValue(),
            'tasks' => [
                'results' => $this->normalizer->normalize($tasks->getResults()),
                'totalCount' => $tasks->getTotalCount(),
                'pageNumber' => $tasks->getPageNumber(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof TaskCollection;
    }
}
