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
            [[['columnId' => 1]], false],
            [[['columnId' => 1, 'direction' => 'NONE']], false],
            [[['columnId' => 'a', 'direction' => 'ASC']], false],
            [[['badKey' => 1, 'direction' => 'ASC']], false],
            [[['columnId' => 1, 'badKey' => 'ASC']], false],
            [[['columnId' => 1, 'direction' => 'ASC', 'language'=> 'PL']], true],
            [[['columnId' => 1, 'direction' => 'DESC', 'language'=> 'PL']], true],
            [
                [
                    ['columnId' => 1, 'direction' => 'DESC', 'language'=> 'PL'],
                    ['columnId' => 2, 'direction' => 'ASC', 'language'=> 'PL'],
                ],
                true,
            ],
            [
                [
                    ['columnId' => 1, 'direction' => 'DESC', 'language'=> 'PL'],
                    ['columnId' => 2, 'direction' => 'NONE', 'language'=> 'PL'],
                ],
                false,
            ],
            [[['columnId' => 'id', 'direction' => 'ASC', 'language'=> 'PL']], true],
            [[['columnId' => 'kindId', 'direction' => 'ASC', 'language'=> 'PL']], true],
            [[['columnId' => 'a', 'direction' => 'ASC', 'language'=> 'PL']], false],
            [[['columnId' => 'z', 'direction' => 'ASC', 'language'=> 1]], false],
        ];
    }
}
