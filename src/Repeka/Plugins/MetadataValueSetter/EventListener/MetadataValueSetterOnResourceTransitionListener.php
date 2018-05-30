<?php
namespace Repeka\Plugins\MetadataValueSetter\EventListener;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Plugins\MetadataValueSetter\Model\RepekaMetadataValueSetterResourceWorkflowPlugin;

class MetadataValueSetterOnResourceTransitionListener {
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

    public function onResourceTransition(BeforeCommandHandlingEvent $event) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $newResourceContents = $command->getContents();
        $workflow = $resource->getWorkflow();
        if ($workflow) {
            $targetPlaces = EntityUtils::filterByIds($command->getTransition()->getToIds(), $workflow->getPlaces());
            $content = $resource->getContents();
            $metadataNames = array_filter($this->configuration->getOptionFromPlaces('metadataName', $targetPlaces));
            $metadataValues = array_filter($this->configuration->getOptionFromPlaces('metadataValue', $targetPlaces));
            if ($metadataNames && $metadataValues) {
                foreach ($metadataNames as $key => $value) {
                    try {
                        $metadata = $resource->getKind()->getMetadataByIdOrName($value);
                        $value = $this->strategyEvaluator->render($resource, $metadataValues[$key]);
                        if (!in_array($value, $newResourceContents->getValues($metadata))) {
                            $newResourceContents = $content->withMergedValues($metadata, $value);
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
