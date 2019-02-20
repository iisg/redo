<?php
namespace Repeka\Tests\Integration\ResourceWorkflow;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

abstract class ResourceWorkflowPluginIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /**
     * @param $resource ResourceEntity
     * @param $pluginConfiguration
     */
    protected function usePluginWithResource(ResourceEntity $resource, array $pluginConfiguration) {
        $workflow = $resource->getWorkflow();
        $places = array_map(
            function (ResourceWorkflowPlace $place) use ($pluginConfiguration) {
                return ResourceWorkflowPlace::fromArray(
                    array_merge(
                        $place->toArray(),
                        [
                            'pluginsConfig' => $pluginConfiguration,
                        ]
                    )
                );
            },
            $workflow->getPlaces()
        );
        $this->handleCommandBypassingFirewall(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $places,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
        $this->handleCommandBypassingFirewall(
            new ResourceTransitionCommand(
                $resource,
                $resource->getContents(),
                SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource),
                $this->getAdminUser()
            )
        );
    }
}
