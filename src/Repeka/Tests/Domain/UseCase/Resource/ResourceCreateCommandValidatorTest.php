<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings(PHPMD.LongVariable) */
class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind */
    private $resourceKind;
    private $resourceClass;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
        $this->resourceClass = 'books';
    }

    private function createValidator(
        bool $valueSetMatchesResourceKind,
        bool $metadataValuesSatisfyConstraints,
        bool $resourceClassExists
    ): ResourceCreateCommandValidator {
        $valueSetMatchesResourceKindRule = $this->createRuleWithFactoryMethodMock(
            ValueSetMatchesResourceKindRule::class,
            'forResourceKind',
            $valueSetMatchesResourceKind
        );
        $metadataValuesSatisfyConstraintsRule = $this->createRuleWithFactoryMethodMock(
            MetadataValuesSatisfyConstraintsRule::class,
            'forResourceKind',
            $metadataValuesSatisfyConstraints
        );
        $resourceClassExistsRule = $this->createRuleMock(
            ResourceClassExistsRule::class,
            $resourceClassExists
        );
        return new ResourceCreateCommandValidator(
            $valueSetMatchesResourceKindRule,
            $metadataValuesSatisfyConstraintsRule,
            $resourceClassExistsRule
        );
    }

    public function testValid() {
        $validator = $this->createValidator(true, true, true);
        $command = new ResourceCreateCommand($this->resourceKind, [1 => ['Some value']], $this->resourceClass);
        $validator->validate($command);
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = $this->createValidator(true, true, true);
        $command = new ResourceCreateCommand(new ResourceKind([], $this->resourceClass), [1 => ['Some value']], $this->resourceClass);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenNoContents() {
        $validator = $this->createValidator(true, true, true);
        $command = new ResourceCreateCommand($this->resourceKind, [], $this->resourceClass);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidIfContentsDoNotMatchResourceKind() {
        $validator = $this->createValidator(false, true, true);
        $command = new ResourceCreateCommand($this->resourceKind, [1 => ['Some value']], $this->resourceClass);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenConstraintsNotSatisfied() {
        $validator = $this->createValidator(true, false, true);
        $command = new ResourceCreateCommand($this->resourceKind, [], $this->resourceClass);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $validator = $this->createValidator(true, true, false);
        $command = new ResourceCreateCommand($this->resourceKind, [], 'invalidResourceClass');
        $this->assertFalse($validator->isValid($command));
    }
}
