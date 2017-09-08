<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings(PHPMD.LongVariable) */
class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind */
    private $resourceKind;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
    }

    private function createValidator(
        bool $valueSetMatchesResourceKind,
        bool $metadataValuesSatisfyConstraints
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
        return new ResourceCreateCommandValidator($valueSetMatchesResourceKindRule, $metadataValuesSatisfyConstraintsRule);
    }

    public function testValid() {
        $validator = $this->createValidator(true, true);
        $command = new ResourceCreateCommand($this->resourceKind, [1 => ['Some value']]);
        $validator->validate($command);
    }

    public function testInvalidForNotInitializedResourceKind() {
        $validator = $this->createValidator(true, true);
        $command = new ResourceCreateCommand(new ResourceKind([]), [1 => ['Some value']]);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenNoContents() {
        $validator = $this->createValidator(true, true);
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidIfContentsDoNotMatchResourceKind() {
        $validator = $this->createValidator(false, true);
        $command = new ResourceCreateCommand($this->resourceKind, [1 => ['Some value']]);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenConstraintsNotSatisfied() {
        $validator = $this->createValidator(true, false);
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $this->assertFalse($validator->isValid($command));
    }
}
