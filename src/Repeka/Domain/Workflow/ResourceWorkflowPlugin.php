<?php
namespace Repeka\Domain\Workflow;

use Assert\Assertion;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Cqrs\Event\CqrsCommandEvent;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Factory\Audit;

abstract class ResourceWorkflowPlugin {
    private const AUDIT_ENTRY_PREFIX = 'resource_workflow_plugin-';

    /** @var Audit */
    private $audit;

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

    /** @required */
    public function setAudit(Audit $audit) {
        $this->audit = $audit;
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    protected function newAuditEntry(CqrsCommandEvent $commandEvent, string $pluginEventType, array $data = [], bool $successful = true) {
        $data = array_merge($data, ['pluginEventType' => $pluginEventType, 'workflowPluginName' => $this->getName()]);
        if (!isset($data['resourceId'])) {
            $data['resourceId'] = $commandEvent->getCommand()->getResource()->getId();
        }
        $this->audit->newEntry(
            self::AUDIT_ENTRY_PREFIX . $this->getName(),
            $commandEvent->getCommand()->getExecutor(),
            $data,
            $successful
        );
    }
}
