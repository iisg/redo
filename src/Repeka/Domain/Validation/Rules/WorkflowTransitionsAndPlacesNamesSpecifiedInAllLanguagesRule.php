<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\LanguageRepository;
use Respect\Validation\Rules\AbstractRule;

class WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule extends AbstractRule {
    /** @var LanguageRepository */
    private $languageRepository;
    /** @var ResourceWorkflowPlace[] */
    private $places;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param ResourceWorkflowPlace[]|array[] $places
     * @return WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
     */
    public function withPlaces(array $places): WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule {
        $instance = new self($this->languageRepository);
        $instance->places = array_map(
            function ($placeOrArray) {
                return is_a($placeOrArray, ResourceWorkflowPlace::class)
                    ? $placeOrArray
                    : ResourceWorkflowPlace::fromArray($placeOrArray);
            },
            $places
        );
        return $instance;
    }

    /**
     * @param ResourceWorkflowTransition[]|array[] $transitions
     * @return boolean
     */
    public function validate($transitions) {
        Assertion::isArray($this->places, 'You need to instantiate validator instance with withPlaces() method to use it');
        if (is_array($transitions) && count($transitions) > 0 && is_array(current($transitions))) {
            $transitions = array_map(
                function ($transition) {
                    return ResourceWorkflowTransition::fromArray($transition);
                },
                $transitions
            );
        }
        return $this->allLabelsHaveValueInEveryLanguage(
            array_merge($this->places, $transitions)
        );
    }

    private function allLabelsHaveValueInEveryLanguage($subjects) {
        $definedLanguageCodes = $this->languageRepository->getAvailableLanguageCodes();
        foreach ($subjects as $subject) {
            foreach ($definedLanguageCodes as $languageCode) {
                if (!isset($subject->getLabel()[$languageCode]) || $subject->getLabel()[$languageCode] === '') {
                    return false;
                }
            }
        }
        return true;
    }
}
