<?php
namespace Repeka\Plugins\Ocr\EventListener;

use Repeka\Application\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Plugins\Ocr\Model\OcrCommunicator;
use Repeka\Plugins\Ocr\Model\RepekaOcrResourceWorkflowPlugin;

class OcrOnResourceTransitionListener {
    /** @var OcrCommunicator */
    private $communicator;
    /** @var RepekaOcrResourceWorkflowPlugin */
    private $configuration;

    public function __construct(RepekaOcrResourceWorkflowPlugin $configuration) {
        $this->configuration = $configuration;
    }

    /** @required */
    public function setCommunicator(OcrCommunicator $communicator) {
        $this->communicator = $communicator;
    }

    public function onResourceTransition(CommandHandledEvent $event) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $metadataToOcr = array_filter($this->configuration->getOption('metadataToOcr', $resource));
        if ($metadataToOcr) {
            foreach ($metadataToOcr as $metadataId) {
                try {
                    $values = $resource->getValues($resource->getKind()->getMetadataByIdOrName($metadataId));
                    $this->communicator->sendToOcr($values);
                } catch (\InvalidArgumentException $e) {
                }
            }
        }
    }
}
