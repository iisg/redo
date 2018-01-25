<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandValidator;
use Repeka\Domain\Validation\Rules\LockedMetadataValuesAreUnchangedRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings(PHPMD.LongVariable) */
class ResourceUpdateContentsCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $this->resource = $this->createResourceMock(1, $resourceKind);
    }

    private function createValidator(
        bool $valueSetMatchesResourceKind,
        bool $metadataValuesSatisfyConstraints,
        bool $lockedMetadataValuesAreUnchanged,
        bool $resourceContentsCurrentStructure
    ): ResourceUpdateContentsCommandValidator {
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
        $lockedMetadataValuesAreUnchangedRule = $this->createRuleWithFactoryMethodMock(
            LockedMetadataValuesAreUnchangedRule::class,
            'forResource',
            $lockedMetadataValuesAreUnchanged
        );
        $resourceContentsCorrectStructureRule = $this->createRuleMock(
            ResourceContentsCorrectStructureRule::class,
            $resourceContentsCurrentStructure
        );
        return new ResourceUpdateContentsCommandValidator(
            $valueSetMatchesResourceKindRule,
            $metadataValuesSatisfyConstraintsRule,
            $lockedMetadataValuesAreUnchangedRule,
            $resourceContentsCorrectStructureRule
        );
    }

    public function testValid() {
        $validator = $this->createValidator(true, true, true, true);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $validator->validate($command);
    }

    public function testInvalidIfContentsDoNotMatchResourceKind() {
        $this->expectException(InvalidCommandException::class);
        $validator = $this->createValidator(false, true, true, true);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $validator->validate($command);
    }

    public function testInvalidWhenConstraintsNotSatisfied() {
        $this->expectException(InvalidCommandException::class);
        $validator = $this->createValidator(true, false, true, true);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $validator->validate($command);
    }

    public function testInvalidWhenLockedMetadataAreChanged() {
        $this->expectException(InvalidCommandException::class);
        $validator = $this->createValidator(true, true, false, true);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $validator->validate($command);
    }

    public function testInvalidWhenInvalidContentsStructure() {
        $this->expectException(InvalidCommandException::class);
        $validator = $this->createValidator(true, true, true, false);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $validator->validate($command);
    }
}
