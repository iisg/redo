<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Tests\Traits\StubsTrait;

class ContainsParentMetadataRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ContainsParentMetadataRule */
    private $validator;

    protected function setUp() {
        $this->validator = new ContainsParentMetadataRule();
    }

    public function testRejectsArrayWithoutParentMetadata() {
        $metadataList = [
            $this->createMetadataMock(1),
            $this->createMetadataMock(2),
        ];
        $this->assertFalse($this->validator->validate($metadataList));
    }

    public function testAcceptsArrayWithParentMetadata() {
        $metadataList = [
            SystemMetadata::PARENT()->toMetadata(),
            $this->createMetadataMock(),
        ];
        $this->assertTrue($this->validator->validate($metadataList));
    }
}
