<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;

class ContainsParentMetadataRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ContainsParentMetadataRule */
    private $validator;

    protected function setUp() {
        $this->validator = new ContainsParentMetadataRule();
    }

    public function testRejectsNonExistingResourceClasses() {
        $metadataList = [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 2, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ];
        $this->assertFalse($this->validator->validate($metadataList));
    }

    public function testAcceptsExistingResourceClass() {
        $metadataList = [
            [
                'baseId' => SystemMetadata::PARENT,
                'name' => 'A',
                'label' => ['PL' => 'Label A'],
                'description' => [],
                'placeholder' => [],
                'control' => 'text'
            ],
            ['baseId' => 2, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ];
        $this->assertTrue($this->validator->validate($metadataList));
    }
}
