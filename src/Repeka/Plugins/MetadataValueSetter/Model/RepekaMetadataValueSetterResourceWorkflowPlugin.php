<?php
namespace Repeka\Plugins\MetadataValueSetter\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandAdjuster;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaMetadataValueSetterResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @var ResourceDisplayStrategyEvaluator */
    private $strategyEvaluator;
    /** @var ResourceTransitionCommandAdjuster */
    private $resourceTransitionCommandAdjuster;

    public function __construct(
        ResourceTransitionCommandAdjuster $resourceTransitionCommandAdjuster,
        ResourceDisplayStrategyEvaluator $strategyEvaluator
    ) {
        $this->strategyEvaluator = $strategyEvaluator;
        $this->resourceTransitionCommandAdjuster = $resourceTransitionCommandAdjuster;
    }

    public function beforeEnterPlace(BeforeCommandHandlingEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $newResourceContents = $command->getContents();
        $metadataName = $config->getConfigValue('metadataName');
        $metadataValue = $config->getConfigValue('metadataValue');
        $setOnlyWhenEmpty = $config->getConfigValue('setOnlyWhenEmpty');
        if (!$metadataName || !$metadataValue) {
            return;
        }
        try {
            $metadata = $resource->getKind()->getMetadataByIdOrName($metadataName);
            $value = $this->strategyEvaluator->render(
                $newResourceContents,
                $metadataValue,
                null,
                ['command' => $command, 'resourceBeforeTransition' => $resource]
            );
            $value = trim($value);
            $sameValueExists = in_array($value, $newResourceContents->getValuesWithoutSubmetadata($metadata));
            $anyValueExists = !empty($newResourceContents->getValues($metadata));
            $blockSettingNonEmpty = $setOnlyWhenEmpty && $anyValueExists;
            if ($value !== '' && !$sameValueExists && !$blockSettingNonEmpty) {
                $newResourceContents = $newResourceContents->withMergedValues($metadata, $value);
            }
        } catch (\InvalidArgumentException $e) {
        }
        $newCommand = new ResourceTransitionCommand($resource, $newResourceContents, $command->getTransition(), $command->getExecutor());
        $newCommand = $this->resourceTransitionCommandAdjuster->adjustCommand($newCommand);
        $event->replaceCommand($newCommand);
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('metadataName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('metadataValue', MetadataControl::TEXTAREA()),
            new ResourceWorkflowPluginConfigurationOption('setOnlyWhenEmpty', MetadataControl::BOOLEAN()),
        ];
    }
}
