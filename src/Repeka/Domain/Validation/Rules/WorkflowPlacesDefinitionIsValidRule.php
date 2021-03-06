<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class WorkflowPlacesDefinitionIsValidRule extends AbstractRule {

    /** @var EntityExistsRule */
    private $entityExistsRule;

    public function __construct(EntityExistsRule $entityExistsRule) {
        $this->entityExistsRule = $entityExistsRule;
    }

    public function validate($input): bool {
        $metadataExistsRule = $this->entityExistsRule->forEntityType(Metadata::class);
        return Validator::arrayType()->length(1)->each(
            Validator::oneOf(
                Validator::instance(ResourceWorkflowPlace::class),
                Validator::arrayType()->keySet(
                    Validator::key('label', Validator::arrayType()),
                    Validator::key('id', Validator::stringType(), false),
                    Validator::key('requiredMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                    Validator::key('lockedMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                    Validator::key('assigneeMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                    Validator::key('autoAssignMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                    Validator::key(
                        'pluginsConfig',
                        Validator::arrayType()->each(
                            Validator::arrayType()->keySet(
                                Validator::key('name', Validator::stringType()->notBlank()),
                                Validator::key('config', Validator::arrayType())
                            )
                        ),
                        false
                    )
                )
            )
        )->each(Validator::callback([$this, 'noCommonValuesBetweenRequirements']))->validate($input);
    }

    public function noCommonValuesBetweenRequirements($place): bool {
        $merged = ($place instanceof ResourceWorkflowPlace)
            ? array_merge($place->restrictingMetadataIds()->all()->get(false))
            : array_merge(
                $place['requiredMetadataIds'] ?? [],
                $place['lockedMetadataIds'] ?? [],
                $place['assigneeMetadataIds'] ?? [],
                $place['autoAssignMetadataIds'] ?? []
            );
        $counts = array_count_values($merged);
        $duplicates = array_filter(
            $counts,
            function ($count) {
                return $count > 1;
            }
        );
        Assertion::noContent(
            $duplicates,
            'These metadata appear in multiple resource place constraints: ' . implode(', ', array_keys($duplicates))
        );
        return true;
    }
}
