<?php
namespace Repeka\Plugins\Ocr\Model;

use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaOcrResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @var OcrCommunicator */
    private $communicator;

    /** @required */
    public function setCommunicator(OcrCommunicator $communicator) {
        $this->communicator = $communicator;
    }

    public function afterEnterPlace(CommandHandledEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $metadataToOcr = $config->getConfigValue('metadataToOcr');
        if ($metadataToOcr) {
            try {
                $values = $resource->getValues($resource->getKind()->getMetadataByIdOrName($metadataToOcr));
                $this->communicator->sendToOcr($values, $config);
            } catch (\InvalidArgumentException $e) {
            }
        }
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('metadataToOcr', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('metadataForResult', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('transitionAfterOcr', MetadataControl::TEXT()),
        ];
    }
}
