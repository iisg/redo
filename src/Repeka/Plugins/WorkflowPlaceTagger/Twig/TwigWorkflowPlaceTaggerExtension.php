<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Twig;

use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggedPath;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerHelper;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerResourceWorkflowPlugin;

class TwigWorkflowPlaceTaggerExtension extends \Twig_Extension {
    use CurrentUserAware;

    /** @var WorkflowPlaceTaggerHelper */
    private $workflowPlaceTaggerHelper;

    public function __construct(WorkflowPlaceTaggerHelper $workflowPlaceTaggerHelper) {
        $this->workflowPlaceTaggerHelper = $workflowPlaceTaggerHelper;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getTaggedPlaces', [$this, 'getTaggedPlaces']),
            new \Twig_Function('getWorkflowPlaceTaggedPath', [$this, 'getWorkflowPlaceTaggedPath']),
        ];
    }

    /**
     * @param string $tagName
     * @param ResourceEntity|ResourceKind|ResourceWorkflow $subject
     * @return WorkflowPlaceTaggedPath
     */
    public function getWorkflowPlaceTaggedPath(string $tagName, $subject): ?WorkflowPlaceTaggedPath {
        $workflow = $this->workflowPlaceTaggerHelper->obtainWorkflow($subject);
        try {
            if ($workflow) {
                return new WorkflowPlaceTaggedPath($tagName, $workflow, $this->workflowPlaceTaggerHelper);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $tagName
     * @param ResourceEntity|ResourceKind|ResourceWorkflow $subject
     * @return array
     */
    public function getTaggedPlaces(string $tagName, $subject): array {
        $workflow = $this->workflowPlaceTaggerHelper->obtainWorkflow($subject);
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
}
