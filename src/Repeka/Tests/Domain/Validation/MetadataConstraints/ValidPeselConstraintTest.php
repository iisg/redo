<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Validation\MetadataConstraints\ValidPeselConstraint;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

class ValidPeselConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** * @var ValidPeselConstraint */
    private $validPeselConstraint;
    /** * @var Metadata */
    private $metadata;

    public function setUp() {
        $this->validPeselConstraint = new ValidPeselConstraint();
        $this->metadata = $this->createMetadataMock(1, null, MetadataControl::TEXT(), ['validPesel' => true]);
    }

    public function testConfigMustBeBoolean() {
        $this->assertTrue($this->validPeselConstraint->isConfigValid(true));
        $this->assertTrue($this->validPeselConstraint->isConfigValid(false));
        $this->assertFalse($this->validPeselConstraint->isConfigValid('false'));
        $this->assertFalse($this->validPeselConstraint->isConfigValid(234));
    }

    public function testAcceptsMetadataValuesThatAreNotPesel() {
        $notPeselMetadata = $this->createMetadataMock(1, null, MetadataControl::TEXT(), ['validPesel' => false]);
        $this->validPeselConstraint->validateSingle($notPeselMetadata, 'some text');
    }

    public function testRejectsPeselNumbersWithInvalidStructure() {
        $this->expectException(DomainException::class);
        $this->validPeselConstraint->validateSingle($this->metadata, '12345');
        $this->validPeselConstraint->validateSingle($this->metadata, '970930098b1');
    }

    public function testRejectsPeselNumbersWithInvalidChecksum() {
        $this->expectException(DomainException::class);
        $this->validPeselConstraint->validateSingle($this->metadata, '90060804796');
        $this->validPeselConstraint->validateSingle($this->metadata, '80080517455');
    }

    public function testRejectsPeselNumbersWithCorrectChecksumButIncorrectDate() {
        $this->expectException(DomainException::class);
        $testPesel1 = '50023017455';
        $testPesel2 = '45631314764';
        $this->assertTrue(Validator::pesel()->validate($testPesel1));
        $this->assertTrue(Validator::pesel()->validate($testPesel2));
        $this->validPeselConstraint->validateSingle($this->metadata, $testPesel1);
        $this->validPeselConstraint->validateSingle($this->metadata, $testPesel2);
    }

    public function testRejectsPeselNumbersWithCorrectChecksumButFutureDate() {
        $this->expectException(DomainException::class);
        $testPesel1 = '76290217455';
        $testPesel2 = '95131314764';
        $this->assertTrue(Validator::pesel()->validate($testPesel1));
        $this->assertTrue(Validator::pesel()->validate($testPesel2));
        $this->validPeselConstraint->validateSingle($this->metadata, $testPesel1);
        $this->validPeselConstraint->validateSingle($this->metadata, $testPesel2);
    }

    public function testAcceptsCorrectPeselNumbers() {
        $correctPesels = [
            '90090515836',
            '92071314764',
            '81100216357',
            '80072909146',
        ];
        foreach ($correctPesels as $pesel) {
            $this->validPeselConstraint->validateSingle($this->metadata, $pesel);
        }
    }
}
