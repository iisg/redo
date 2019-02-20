<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Model;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;

class WorkflowPlaceTaggerHelper {
    /**
     * @param ResourceEntity|ResourceKind|ResourceWorkflow $subject
     * @param string $tagName
     * @return array [ [place => ResourceWorkflowPlace, tagValues: string[]] ]
     */
    public function getTaggedPlaces(string $tagName, $subject): array {
        if (!($workflow = $this->obtainWorkflow($subject))) {
            return [];
        }
        $result = [];
        foreach ($workflow->getPlaces() as $place) {
            $tagValues = [];
            foreach ($place->getPluginConfigs(WorkflowPlaceTaggerResourceWorkflowPlugin::class) as $config) {
                if ($config->getConfigValue('tagName') == $tagName) {
                    $tagValues[] = $config->getConfigValue('tagValue');
                }
            }
            if ($tagValues) {
                $result[] = ['place' => $place, 'tagValues' => $tagValues];
            }
        }
        return $result;
    }

    /**
     * @param ResourceEntity|ResourceKind|ResourceWorkflow $subject
     * @return ResourceWorkflow|null
     */
    public function obtainWorkflow($subject): ?ResourceWorkflow {
        if ($subject instanceof ResourceEntity) {
            $subject = $subject->getKind();
        }
        if ($subject instanceof ResourceKind) {
            $subject = $subject->getWorkflow();
        }
        if (!($subject instanceof ResourceWorkflow)) {
            $subject = null;
        }
        return $subject;
    }
}
