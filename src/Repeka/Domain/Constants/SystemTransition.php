<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @method static SystemTransition CREATE()
 * @method static SystemTransition UPDATE()
 * @method static SystemTransition DELETE()
 */
class SystemTransition extends Enum {
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';

    public function toTransition($resourceKindOrResource, ?ResourceEntity $resource = null): ResourceWorkflowTransition {
        if ($resourceKindOrResource instanceof ResourceEntity) {
            $resource = $resourceKindOrResource;
            $resourceKindOrResource = $resourceKindOrResource = $resource->getKind();
        }
        $workflow = $resourceKindOrResource->getWorkflow();
        $resourceWorkflowTransition = null;
        $value = $this->getValue();
        if ($value == self::CREATE) {
            $tos = $workflow ? [$workflow->getInitialPlace()->getId()] : [];
            $resourceWorkflowTransition = new ResourceWorkflowTransition([self::CREATE], [], $tos);
        } elseif ($value == self::UPDATE) {
            $froms = $tos = $this->getPlacesIds($resource, $workflow);
            $resourceWorkflowTransition = new ResourceWorkflowTransition([self::UPDATE], $froms, $tos);
        } elseif ($value == self::DELETE) {
            $froms = $this->getPlacesIds($resource, $workflow);
            $resourceWorkflowTransition = new ResourceWorkflowTransition([self::DELETE], $froms, []);
        }
        Assertion::notNull($resourceWorkflowTransition, "Not implemented: transition for value $value");
        return $resourceWorkflowTransition;
    }

    /**
     * @param ResourceEntity $resource
     * @param ResourceWorkflow $workflow
     * @return string[]
     */
    private function getPlacesIds(?ResourceEntity $resource = null, ?ResourceWorkflow $workflow = null): array {
        Assertion::notNull($resource);
        $places = $workflow ? $resource->getWorkflow()->getPlaces($resource) : [];
        return EntityUtils::mapToIds($places);
    }

    public function apply(ResourceEntity $resource) {
        $transition = $this->toTransition($resource->getKind(), $resource);
        $resource->getWorkflow()->setCurrentPlaces($resource, $transition->getToIds());
    }
}
