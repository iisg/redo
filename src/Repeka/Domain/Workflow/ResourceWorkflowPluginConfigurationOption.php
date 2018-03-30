<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\MetadataControl;

class ResourceWorkflowPluginConfigurationOption {
    private $control;
    private $name;

    public function __construct(string $name, MetadataControl $control) {
        $this->name = $name;
        $this->control = $control;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getControl(): MetadataControl {
        return $this->control;
    }
}
