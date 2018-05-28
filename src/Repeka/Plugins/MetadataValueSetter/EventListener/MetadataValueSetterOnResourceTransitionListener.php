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
        $newResourceContents = $resource->getContents();
        $workflow = $resource->getWorkflow();
        if ($workflow) {
            $places = $command->getTransition()->getToIds();
            $allPlaces = $workflow->getPlaces();
            $interestingPlaces = EntityUtils::filterByIds($places, $allPlaces);
            $content = $resource->getContents();
            $metadataNames = array_filter($this->configuration->getOptionFromPlaces('metadataName', $interestingPlaces));
            $metadataValues = array_filter($this->configuration->getOptionFromPlaces('metadataValue', $interestingPlaces));
            if ($metadataNames && $metadataValues) {
                foreach ($metadataNames as $key => $value) {
                    $metadata = $resource->getKind()->getMetadataByIdOrName($value);
                    $value = $this->strategyEvaluator->render($resource, $metadataValues[$key]);
                    if (!in_array($value, $newResourceContents->getValues($metadata))) {
                        $newResourceContents = $content->withMergedValues($metadata, $value);
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
