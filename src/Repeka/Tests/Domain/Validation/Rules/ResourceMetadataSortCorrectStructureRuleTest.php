<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;

class ResourceMetadataSortCorrectStructureRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var  ResourceMetadataSortCorrectStructureRule */
    private $rule;

    /** @before */
    protected function init() {
        $this->rule = new ResourceMetadataSortCorrectStructureRule();
    }

    /** @dataProvider validationExamples */
    public function testValidation($input, bool $expectValid) {
        $this->assertEquals($expectValid, $this->rule->validate($input), var_export($input, true));
    }

    public function validationExamples(): array {
        return [
            [null, false],
            ['', false],
            ['ala ma kota', false],
            [[], true],
            [['ala' => 'kot'], false],
            [['2' => 'kot'], false],
            [[['metadataId' => 1]], false],
            [[['metadataId' => 1, 'direction' => 'NONE']], false],
            [[['metadataId' => 'a', 'direction' => 'ASC']], false],
            [[['badKey' => 1, 'direction' => 'ASC']], false],
            [[['metadataId' => 1, 'badKey' => 'ASC']], false],
            [[['metadataId' => 1, 'direction' => 'ASC']], true],
            [[['metadataId' => 1, 'direction' => 'DESC']], true],
            [
                [
                    ['metadataId' => 1, 'direction' => 'DESC'],
                    ['metadataId' => 2, 'direction' => 'ASC'],
                ],
                true,
            ],
            [
                [
                    ['metadataId' => 1, 'direction' => 'DESC'],
                    ['metadataId' => 2, 'direction' => 'NONE'],
                ],
                false,
            ],
        ];
    }
}
