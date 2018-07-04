<?php
namespace Repeka\Domain\Workflow;

use Assert\Assertion;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;

abstract class ResourceWorkflowPlugin {
    public function getName() {
        return self::getNameFromClassName(get_class($this));
    }

    public static function getNameFromClassName(string $className) {
        $successful = preg_match('#\\\\([a-z]+?)(ResourceWorkflowPlugin)?$#i', $className, $matches);
        Assertion::true(!!$successful);
        return lcfirst($matches[1]);
    }

    /** @inheritdoc */
    public function beforeEnterPlace(BeforeCommandHandlingEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
    }

    /** @inheritdoc */
    public function afterEnterPlace(CommandHandledEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
    }

    /** @inheritdoc */
    public function failedEnterPlace(CommandErrorEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    abstract public function getConfigurationOptions(): array;

    /** @inheritdoc */
    public function supports(ResourceWorkflow $workflow): bool {
        return true;
    }
}
