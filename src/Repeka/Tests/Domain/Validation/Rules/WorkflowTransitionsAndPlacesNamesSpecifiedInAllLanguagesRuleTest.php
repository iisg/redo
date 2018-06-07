<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

/** @SuppressWarnings("PHPMD.LongVariable") */
class WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepositoryStub;

    /** @var WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule */
    private $rule;

    private $enLabeledPlace;
    private $plLabeledPlace;
    private $noneLabeledPlace;
    private $allLabeledPlace;
    private $setLanguageToEmptyStringLabeledPlace;
    private $enLabeledTransition;
    private $plLabeledTransition;
    private $noneLabeledTransition;
    private $allLabeledTransition;
    private $setLanguageToEmptyStringLabeledTransition;

    protected function setUp() {
        $this->languageRepositoryStub = $this->createLanguageRepositoryMock(['PL', 'EN']);
        $this->rule = new WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule($this->languageRepositoryStub);
        $this->enLabeledPlace = $this->createWorkflowPlaceMock('enLabeledPlace', [], [], [], ['EN' => 'EnLabel']);
        $this->plLabeledPlace = $this->createWorkflowPlaceMock('plLabeledPlace', [], [], [], ['PL' => 'PlLabel']);
        $this->allLabeledPlace = $this->createWorkflowPlaceMock('allLabeledPlace', [], [], [], ['PL' => 'PlLabel', 'EN' => 'EnLabel']);
        $this->setLanguageToEmptyStringLabeledPlace =
            $this->createWorkflowPlaceMock('setToEmptyLabeledPlace', [], [], [], ['PL' => 'PlLabel', 'EN' => '']);
        $this->noneLabeledPlace = $this->createWorkflowPlaceMock('noneLabeledPlace', [], [], [], []);
        $this->enLabeledTransition = $this->createWorkflowTransitionMock(['EN' => 'label1'], ['x'], ['y'], 'enLabeledTransition');
        $this->plLabeledTransition = $this->createWorkflowTransitionMock(['PL' => 'plb'], ['a'], ['b'], 'plLabeledTransition');
        $this->allLabeledTransition =
            $this->createWorkflowTransitionMock(['PL' => 'pla', 'EN' => 'ena'], ['x'], ['y'], 'allLabeledTransition');
        $this->setLanguageToEmptyStringLabeledTransition =
            $this->createWorkflowTransitionMock(['PL' => '', 'EN' => 'ena'], ['x'], ['y'], 'setToEmptyLabeledTransition');
        $this->noneLabeledTransition = $this->createWorkflowTransitionMock([], ['a'], ['b'], 'noneLabeledTransition');
    }

    public function testValuesSetInAllLanguages() {
        foreach ($this->placeTransitionExamples() as $testCase) {
            [$places, $transitions, $expectedResult, $name] = $testCase;
            $this->rule = $this->rule->withPlaces($places);
            $this->assertEquals($expectedResult, $this->rule->validate($transitions), "Failed: " . $name);
        }
    }

    private function placeTransitionExamples() {
        return [
            [
                [$this->setLanguageToEmptyStringLabeledPlace],
                [$this->allLabeledTransition],
                false,
                "testWithNameNotSetInPlacePassingValidation",
            ],
            [
                [$this->allLabeledPlace],
                [$this->setLanguageToEmptyStringLabeledTransition],
                false,
                "testWithNameNotSetInTransitionPassingValidation",
            ],
            [
                [$this->allLabeledPlace],
                [$this->plLabeledTransition],
                false,
                "testWithNotNamedTransitionsAndNamedPlacesNotPassingValidation",
            ],
            [
                [$this->noneLabeledPlace],
                [$this->noneLabeledTransition],
                false,
                "testWithNamedTransitionsAndNotNamedPlacesNotPassingValidation",
            ],
            [
                [$this->noneLabeledPlace],
                [$this->enLabeledTransition],
                false,
                "testWithNotNamedTransitionsAndNotNamedPlacesNotPassingValidation",
            ],
            [[$this->allLabeledPlace], [$this->allLabeledTransition], true, "testWithNamedTransitionsAndPlacesPassingValidation"],
        ];
    }
}
