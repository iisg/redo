<?php
namespace Repeka\Plugins\MetadataValueRemover\Model;

use Repeka\Application\Service\PhpRegexNormalizer;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaMetadataValueRemoverResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @var RegexConstraint */
    private $regexConstraint;
    /** @var PhpRegexNormalizer */
    private $phpRegexNormalizer;

    public function __construct(RegexConstraint $regexConstraint, PhpRegexNormalizer $phpRegexNormalizer) {
        $this->regexConstraint = $regexConstraint;
        $this->phpRegexNormalizer = $phpRegexNormalizer;
    }

    public function beforeEnterPlace(BeforeCommandHandlingEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $newResourceContents = $command->getContents();
        $metadataName = $config->getConfigValue('metadataName');
        $metadataValuePattern = $config->getConfigValue('metadataValuePattern');
        if (!$metadataName || !$metadataValuePattern) {
            return;
        }
        try {
            $metadata = $resource->getKind()->getMetadataByIdOrName($metadataName);
            if ($this->regexConstraint->isConfigValid($metadataValuePattern)) {
                $metadataValuePattern = $this->phpRegexNormalizer->normalize($metadataValuePattern);
                $purgedValues = array_values(
                    array_filter(
                        $newResourceContents->getValues($metadata),
                        function (MetadataValue $metadataValue) use ($metadataValuePattern) {
                            return !preg_match($metadataValuePattern, strval($metadataValue->getValue()));
                        }
                    )
                );
                $newResourceContents = $newResourceContents->withReplacedValues($metadata, $purgedValues);
                $event->replaceCommand(
                    new ResourceTransitionCommand($resource, $newResourceContents, $command->getTransition(), $command->getExecutor())
                );
            }
        } catch (\InvalidArgumentException | InvalidCommandException $e) {
        }
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('metadataName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('metadataValuePattern', MetadataControl::TEXT()),
        ];
    }
}
