<?php
namespace Repeka\Plugins\MetadataValueSetter\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Utils\EntityUtils;

class MetadataValueSetterOnResourceTransitionListener extends CommandEventsListener {
    /** @var RepekaMetadataValueSetterResourceWorkflowPlugin */
    private $configuration;
    /** @var ResourceDisplayStrategyEvaluator */
    private $strategyEvaluator;

    public function __construct(
        RepekaMetadataValueSetterResourceWorkflowPlugin $configuration,
        ResourceDisplayStrategyEvaluator $strategyEvaluator
    ) {
        $this->configuration = $configuration;
        $this->strategyEvaluator = $strategyEvaluator;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $newResourceContents = $command->getContents();
        $workflow = $resource->getWorkflow();
        if ($workflow) {
            $targetPlaces = EntityUtils::filterByIds($command->getTransition()->getToIds(), $workflow->getPlaces());
            $metadataNames = array_filter($this->configuration->getOptionFromPlaces('metadataName', $targetPlaces));
            $metadataValues = array_filter($this->configuration->getOptionFromPlaces('metadataValue', $targetPlaces));
            if ($metadataNames && $metadataValues) {
                foreach ($metadataNames as $key => $value) {
                    try {
                        $metadata = $resource->getKind()->getMetadataByIdOrName($value);
                        $value = $this->strategyEvaluator->render($newResourceContents, $metadataValues[$key]);
                        if (!in_array($value, $newResourceContents->getValues($metadata))) {
                            $newResourceContents = $newResourceContents->withMergedValues($metadata, $value);
                        }
                    } catch (\InvalidArgumentException $e) {
                    }
                }
                $event->replaceCommand(
                    new ResourceTransitionCommand(
                        $resource,
                        $newResourceContents,
                        $command->getTransition(),
                        $command->getExecutor()
                    )
                );
            }
        }
    }
}
