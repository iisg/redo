<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
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
        bool $resourceContentsCurrectStructure
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
        $resourceContentsCorrectStructureRule = $this->createRuleMock(
            ResourceContentsCorrectStructureRule::class,
            $resourceContentsCurrectStructure
        );
        return new ResourceCreateCommandValidator(
            $valueSetMatchesResourceKindRule,
            $metadataValuesSatisfyConstraintsRule,
            $resourceContentsCorrectStructureRule
        );
    }

    public function testValid() {
        $validator = $this->createValidator(true, true, true);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->assertTrue($validator->isValid($command));
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = $this->createValidator(true, true, true);
        $command = new ResourceCreateCommand($this->createMock(ResourceKind::class), ResourceContents::fromArray([1 => ['Some value']]));
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidIfContentsDoNotMatchResourceKind() {
        $validator = $this->createValidator(false, true, true);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenConstraintsNotSatisfied() {
        $validator = $this->createValidator(true, false, true);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenInvalidContentStructure() {
        $validator = $this->createValidator(true, true, false);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->assertFalse($validator->isValid($command));
    }
}
