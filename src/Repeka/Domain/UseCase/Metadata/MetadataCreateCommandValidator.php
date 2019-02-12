<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\MetadataGroupExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceDisplayStrategySyntaxValidRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class MetadataCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var IsValidControlRule */
    private $isValidControlRule;
    /** @var ConstraintArgumentsAreValidRule */
    private $constraintArgumentsAreValidRule;
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var MetadataGroupExistsRule */
    private $metadataGroupExistsRule;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceDisplayStrategySyntaxValidRule */
    private $displayStrategySyntaxValidRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        IsValidControlRule $isValidControlRule,
        ConstraintArgumentsAreValidRule $constraintArgumentsAreValidRule,
        ResourceClassExistsRule $resourceClassExistsRule,
        MetadataGroupExistsRule $metadataGroupExistsRule,
        MetadataRepository $metadataRepository,
        ResourceDisplayStrategySyntaxValidRule $displayStrategySyntaxValidRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->isValidControlRule = $isValidControlRule;
        $this->constraintArgumentsAreValidRule = $constraintArgumentsAreValidRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->metadataGroupExistsRule = $metadataGroupExistsRule;
        $this->metadataRepository = $metadataRepository;
        $this->displayStrategySyntaxValidRule = $displayStrategySyntaxValidRule;
    }

    /**
     * @inheritdoc
     * @param MetadataCreateCommand $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute(
                'name',
                Validator::notBlank()
                    ->callback([$this, 'metadataNameIsUnique'])
                    ->setTemplate('metadataNameIsNotUnique')
            )
            ->attribute(
                'controlName',
                Validator::callback(
                    function ($control) {
                        return MetadataControl::isValid($control);
                    }
                )
            )
            ->attribute('shownInBrief', Validator::boolType())
            ->attribute('copyToChildResource', Validator::boolType())
            ->attribute('resourceClass', $this->resourceClassExistsRule)
            ->attribute('constraints', $this->constraintArgumentsAreValidRule)
            ->attribute('groupId', $this->metadataGroupExistsRule)
            ->attribute('displayStrategy', $this->displayStrategySyntaxValidRule);
    }

    public function metadataNameIsUnique(string $name) {
        $query = MetadataListQuery::builder()->filterByName($name)->build();
        $result = $this->metadataRepository->findByQuery($query);
        return empty($result);
    }
}
