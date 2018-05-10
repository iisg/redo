<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\InvalidArgumentException;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class WorkflowTransitionNamesMatchInAllLanguagesRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepositoryStub;
    /** @var WorkflowTransitionNamesMatchInAllLanguagesRule */
    private $rule;

    protected function setUp() {
        $this->languageRepositoryStub = $this->createLanguageRepositoryMock(['PL', 'EN']);
        $this->rule = new WorkflowTransitionNamesMatchInAllLanguagesRule($this->languageRepositoryStub);
        $this->rule = $this->rule->withPlaces(
            [
                $this->createWorkflowPlaceMock('x'),
                $this->createWorkflowPlaceMock('y'),
                $this->createWorkflowPlaceMock('z'),
                $this->createWorkflowPlaceMock('s'),
                $this->createWorkflowPlaceMock('a'),
                $this->createWorkflowPlaceMock('b'),
                $this->createWorkflowPlaceMock('from place'),
                $this->createWorkflowPlaceMock('place 1'),
                $this->createWorkflowPlaceMock('place 2'),
                $this->createWorkflowPlaceMock('labelledPlace', [], [], [], ['PL' => 'PlLabel', 'EN' => 'EnLabel']),
            ]
        );
    }

    public function testNoTransitionsPassingValidation() {
        $this->assertTrue($this->rule->validate([]));
    }

    public function testSeparateTransitionsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pla', 'EN' => 'ena'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'plb', 'EN' => 'enb'], ['a'], ['b'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testDifferentLabelsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pla', 'EN' => 'ena'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'plb', 'EN' => 'enb'], ['x'], ['y'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testMatchingLabelsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en'], ['x'], ['y'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testDifferentLabelsNotPassingValidation() {
        $this->expectException(DomainException::class);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'different'], ['x'], ['y'], 'tranXX'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'not ok'], ['z'], ['y'], 'tranYY'),
        ];
        $this->rule->validate($transitions);
    }

    public function testDifferentLabelsInSeparateDirectionsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'different'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'ok'], ['y'], ['x'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testDifferentLabelsBetweenDistinctPlacesPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'different'], ['x'], ['y'], 'tranXX'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'ok'], ['a'], ['b'], 'tranYY'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testMissingLabelsInLanguagePassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'pl'], ['z'], ['y'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testMissingLabelsInLanguageNotPassingValidation() {
        $this->expectException(DomainException::class);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'not empty'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock([], ['z'], ['y'], 'tran2'),
        ];
        $this->rule->validate($transitions);
    }

    public function testValidMatchingGroupedTransitionsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en'], ['x', 'y'], ['z'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en'], ['s'], ['z'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testValidGroupedTransitionsPassingValidation() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pla', 'EN' => 'ena'], ['x', 'y'], ['z'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'plb', 'EN' => 'enb'], ['s'], ['z'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testDifferentGroupedTransitionsNotPassingValidation() {
        $this->expectException(DomainException::class);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'different', 'EN' => 'en'], ['x', 'y'], ['z'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'not ok', 'EN' => 'en'], ['s'], ['z'], 'tran2'),
        ];
        $this->rule->validate($transitions);
    }

    public function testIgnoringExtraLanguagesInLabels() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en', 'HU' => 'different'], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'en', 'HU' => 'ok'], ['z'], ['y'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testMissingPlaceLabelsThrowingException() {
        $this->expectException(InvalidArgumentException::class);
        $rule = $this->rule->withPlaces([$this->createWorkflowPlaceMock('x')]);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'XX', 'EN' => 'en'], ['x'], ['place w/out label'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'YY', 'EN' => 'en'], ['z'], ['place w/out label'], 'tran2'),
        ];
        $rule->validate($transitions);
    }

    public function testDifferentTransitionsFromNotPassingValidation() {
        $this->expectException(DomainException::class);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'XX', 'EN' => 'en'], ['from place'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'YY', 'EN' => 'en'], ['from place'], ['z'], 'tran2'),
        ];
        $this->rule->validate($transitions);
    }

    public function testDifferentTransitionsBetweenSamePlacesNotPassingValidation() {
        $this->expectException(DomainException::class);
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'XX', 'EN' => 'en'], ['place 1'], ['place 2'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => 'YY', 'EN' => 'en'], ['place 1'], ['place 2'], 'tran2'),
        ];
        $this->rule->validate($transitions);
    }

    public function testMissingLabelEqualToEmpty() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => '', 'EN' => ''], ['x'], ['y'], 'tran1'),
            $this->createWorkflowTransitionMock(['PL' => ''], ['z'], ['y'], 'tran2'),
        ];
        $this->assertTrue($this->rule->validate($transitions));
    }

    public function testErrorMessageContainsCorrectParameters() {
        $transitions = [
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'different'], ['x'], ['labelledPlace'], 'tranXX'),
            $this->createWorkflowTransitionMock(['PL' => 'pl', 'EN' => 'not ok'], ['z'], ['labelledPlace'], 'tranYY'),
        ];
        try {
            $this->rule->validate($transitions);
            $this->fail('The line above should throw an exception.');
        } catch (DomainException $e) {
            $params = implode($e->getParams());
            $this->assertContains('PL', $params);
            $this->assertContains('pl', $params);
            $this->assertContains('EN', $params);
            $this->assertContains('different', $params);
            $this->assertContains('not ok', $params);
            $this->assertContains('PlLabel', $params);
            $this->assertContains('EnLabel', $params);
        }
    }
}
