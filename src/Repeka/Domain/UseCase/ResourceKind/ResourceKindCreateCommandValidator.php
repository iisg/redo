<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends ResourceKindUpdateCommandValidator {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        MetadataUpdateCommandValidator $metadataUpdateCommandValidator,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule,
        ResourceKindRepository $resourceKindRepository
    ) {
        parent::__construct(
            $notBlankInAllLanguagesRule,
            $containsParentMetadataRule,
            $metadataUpdateCommandValidator,
            $childResourceKindsAreOfSameResourceClassRule
        );
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @param ResourceKindCreateCommand $command */
    public function getValidator(Command $command): Validatable {
        $validator = parent::getValidator($command);
        $validator = $validator->attribute(
            'name',
            Validator::notBlank()
                ->callback([$this, 'nameIsUnique'])
                ->setTemplate('resourceKindNameIsNotUnique')
        );
        return $validator;
    }

    public function nameIsUnique(string $name) {
        $query = ResourceKindListQuery::builder()
            ->filterByNames([$name])
            ->build();
        $count = $this->resourceKindRepository->countByQuery($query);
        return $count === 0;
    }
}
