<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Respect\Validation\Rules\AbstractRule;

/**
 * If metadata is a relationship and is depended upon by any workflow (some
 * workflow uses it to determine assignees), then ensure that relationship
 * is restricted to users.
 * If metadata is not a relationship or no workflows depend on it, returns
 * success.
 */
class ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule extends AbstractRule {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;
    /** @var int */
    private $metadataId;

    public function __construct(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
    }

    public function forMetadataId(int $metadataId): ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule {
        $instance = new self($this->workflowRepository);
        $instance->metadataId = $metadataId;
        return $instance;
    }

    public function validate($metadataConstraints) {
        Assertion::notNull(
            $this->metadataId,
            'Metadata ID not set. Use forMetadataId() for create validator for specific metadata first.'
        );
        if (!array_key_exists('resourceKind', $metadataConstraints)) {
            return true;  // this validator is applicable only to relationships
        }
        $determiningWorkflows = $this->workflowRepository->findByAssigneeMetadata($this->metadataId);
        if (empty($determiningWorkflows)) {
            return true;
        }
        $resourceKindConstraint = $metadataConstraints['resourceKind'];
        return $resourceKindConstraint == [SystemResourceKind::USER];
    }
}
