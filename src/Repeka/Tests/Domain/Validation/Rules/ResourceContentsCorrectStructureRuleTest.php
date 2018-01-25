<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;

class ResourceContentsCorrectStructureRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceContentsCorrectStructureRule */
    private $rule;

    /** @before */
    protected function init() {
        $this->rule = new ResourceContentsCorrectStructureRule();
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
            [[2 => 'kot'], false],
            [['kot'], false],
            [['ala' => ['values' => ['kot']]], false],
            [['2' => [['value' => 'kot']]], true],
            [['value' => 'kot'], false],
            [[['value' => 'kot']], false],
            [[[['value' => 'kot']]], false],
            [[1 => [['value' => ['kot']]]], true],
            [[1 => ['a' => ['value' => ['Some value']]]], false],
            [[1 => ['whatever' => ['Some value']]], false],
            [[1 => ['values' => ['Some value'], 'whatever']], false],
            [[1 => ['values' => ['Some value'], 'whatever' => ['a']]], false],
            [[1 => ['Some value']], false],
            [[1 => ['values' => 'Some value']], false],
            [[1 => ['values' => ['Some value']], 2 => ['values' => ['Some value']]], false],
            [[1 => ['values' => ['Some value']], [2 => ['values' => ['Some value']]]], false],
            [[1 => ['values' => ['Some value'], 'submetadata' => []]], false],
            [[1 => [['value' => ['Some value'], 'submetadata' => []]]], true],
            [[1 => [['value' => ['Some value'], 'submetadata' => ['value' => 'aa']]]], false],
            [[1 => [['value' => ['Some value'], 'submetadata' => [['value' => 'aa']]]]], false],
            [[1 => [['value' => ['Some value'], 'submetadata' => [1 => ['value' => 'aa']]]]], false],
            [[1 => [['value' => ['Some value'], 'submetadata' => [1 => [['value' => 'aa']]]]]], true],
            [[1 => ['values' => ['Some value'], 'submetadata' => ['values' => ['Some value']]]], false],
            [[1 => ['values' => ['Some value'], 'submetadata' => [1 => ['values' => ['Some value']]]]], false],
            [[1 => ['values' => ['Some value'], 'submetadata' => ['values' => ['Some value'], 'submetadata' => []]]], false],
            [
                [
                    1 => [
                        'values' => ['Some value'],
                        'submetadata' => [
                            2 => [
                                'values' => ['Some value'],
                                'submetadata' => ['values' => ['Some value']],
                            ],
                        ],
                    ],
                ],
                false,
            ],
            [
                [
                    1 => [
                        'values' => ['Some value'],
                        'submetadata' => [
                            2 => [
                                'values' => ['Some value'],
                                'submetadata' => [3 => ['values' => ['Some value']]],
                            ],
                        ],
                    ],
                ],
                false,
            ],
            [
                [
                    1 => [
                        [
                            'value' => 'Some value',
                            'submetadata' => [
                                2 => [
                                    [
                                        'value' => 'Some value',
                                        'submetadata' => [3 => [['value' => 'Some value']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                true,
            ],
        ];
    }
}
