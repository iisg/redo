<?php
namespace Repeka\Domain\Workflow;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;

abstract class ResourceWorkflowPlugin {
    public function getName() {
        $successful = preg_match('#\\\\([a-z]+?)(ResourceWorkflowPlugin)?$#i', get_class($this), $matches);
        Assertion::true(!!$successful);
        return lcfirst($matches[1]);
    }

    public function getOption(string $name, ResourceEntity $resource): array {
        if (!$resource->hasWorkflow()) {
            return [];
        }
        $places = $resource->getWorkflow()->getPlaces($resource);
        return $this->getOptionFromPlaces($name, $places);
    }

    public function getOptionFromPlaces(string $name, array $places): array {
        $values = [];
        foreach ($places as $place) {
            $values[$place->getId()] = $place->getPluginConfig($this->getName())[$name] ?? null;
        }
        return $values;
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    abstract public function getConfigurationOptions(): array;

    /** @inheritdoc */
    public function supports(ResourceWorkflow $workflow): bool {
        return true;
    }
}
