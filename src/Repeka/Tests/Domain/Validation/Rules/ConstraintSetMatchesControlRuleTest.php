<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Tests\Traits\StubsTrait;

class ConstraintSetMatchesControlRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    const METADATA_ID = 0;

    /** @var MetadataConstraintManager|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintManager;

    /** @var ConstraintSetMatchesControlRule */
    private $rule;

    public function setUp() {
        $metadataRepository = $this->createRepositoryStub(
            MetadataRepository::class,
            [
                $this->createMetadataMock(self::METADATA_ID, null, MetadataControl::TEXT()),
            ]
        );
        $this->constraintManager = $this->createMock(MetadataConstraintManager::class);
        $this->rule = new ConstraintSetMatchesControlRule($metadataRepository, $this->constraintManager);
    }

    private function configureRequiredConstraints(array $requiredConstraints = ['foo', 'bar']) {
        $this->constraintManager->method('getSupportedConstraintNamesForControl')->willReturn($requiredConstraints);
    }

    public function testRejectsInvalidControls() {
        $this->expectException(\Exception::class);
        $this->rule->forControl('foo');
    }

    public function testFetchesRequiredConstraintsProperly() {
        $this->constraintManager->expects($this->once())->method('getSupportedConstraintNamesForControl')->with('text');
        $this->rule->forControl('text')->validate([]);
    }

    public function testAcceptsValidConstraints() {
        $this->configureRequiredConstraints();
        $this->assertTrue($this->rule->forControl('text')->validate(['foo' => null, 'bar' => null]));
        $this->assertTrue($this->rule->forMetadataId(self::METADATA_ID)->validate(['foo' => null, 'bar' => null]));
    }

    public function testAcceptsValidEmptyConstraints() {
        $this->configureRequiredConstraints([]);
        $this->assertTrue($this->rule->forControl('text')->validate([]));
        $this->assertTrue($this->rule->forMetadataId(self::METADATA_ID)->validate([]));
    }

    public function testAcceptsMissingConstraint() {
        $this->configureRequiredConstraints();
        $this->assertTrue($this->rule->forControl('text')->validate(['foo' => null]));
        $this->assertTrue($this->rule->forControl('text')->validate(['bar' => null]));
        $this->assertTrue($this->rule->forControl('text')->validate([]));
        $this->assertTrue($this->rule->forMetadataId(self::METADATA_ID)->validate(['foo' => null]));
        $this->assertTrue($this->rule->forMetadataId(self::METADATA_ID)->validate(['bar' => null]));
        $this->assertTrue($this->rule->forMetadataId(self::METADATA_ID)->validate([]));
    }

    public function testRejectsExtraConstraints() {
        $this->configureRequiredConstraints();
        $this->assertFalse($this->rule->forControl('text')->validate(['foo' => null, 'bar' => null, 'baz' => null]));
        $this->assertFalse($this->rule->forMetadataId(self::METADATA_ID)->validate(['foo' => null, 'bar' => null, 'baz' => null]));
    }

    public function testRejectsAnyConstraintsWhenNoneRequired() {
        $this->configureRequiredConstraints([]);
        $this->assertFalse($this->rule->forControl('text')->validate(['foo' => null]));
        $this->assertFalse($this->rule->forMetadataId(self::METADATA_ID)->validate(['foo' => null]));
    }
}
