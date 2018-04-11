<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
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
    /** @var Metadata */
    private $metadata;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        ResourceWorkflowRepository $workflowRepository,
        ResourceKindRepository $resourceKindRepository,
        MetadataRepository $metadataRepository
    ) {
        $this->workflowRepository = $workflowRepository;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
    }

    public function forMetadataId(int $metadataId): ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule {
        return $this->forMetadata($this->metadataRepository->findOne($metadataId));
    }

    public function forMetadata(Metadata $metadata): ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule {
        $instance = new self($this->workflowRepository, $this->resourceKindRepository, $this->metadataRepository);
        $instance->metadata = $metadata;
        return $instance;
    }

    public function validate($metadataConstraints) {
        Assertion::notNull(
            $this->metadata,
            'Metadata not set. Use forMetadata() for create validator for specific metadata first.'
        );
        if (!$this->metadata->canDetermineAssignees($this->resourceKindRepository)) {
            return true;
        }
        $determiningWorkflows = $this->workflowRepository->findByAssigneeMetadata($this->metadata);
        if (empty($determiningWorkflows)) {
            return true;
        }
        return $this->metadata->withOverrides(['constraints' => $metadataConstraints])
            ->canDetermineAssignees($this->resourceKindRepository);
    }
}
