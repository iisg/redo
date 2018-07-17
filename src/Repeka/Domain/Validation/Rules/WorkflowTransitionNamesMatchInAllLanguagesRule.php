<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Utils\ArrayUtils;
use Respect\Validation\Rules\AbstractRule;

class WorkflowTransitionNamesMatchInAllLanguagesRule extends AbstractRule {
    /** @var LanguageRepository */
    private $languageRepository;
    /** @var ResourceWorkflowPlace[] */
    private $places;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param ResourceWorkflowPlace[]|array[] $places
     * @return WorkflowTransitionNamesMatchInAllLanguagesRule
     */
    public function withPlaces(array $places): WorkflowTransitionNamesMatchInAllLanguagesRule {
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
     * @return bool
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
        $firstError = $this->findFirstNotMatchingLabels($transitions);
        if ($firstError !== null) {
            throw $this->reportError($transitions, $firstError);
        }
        return true;
    }

    /**
     * @param ResourceWorkflowTransition[] $transitions
     * @return array
     */
    private function findFirstNotMatchingLabels($transitions) {
        $connections = $this->splitTransitions($transitions);
        $languages = $this->languageRepository->getAvailableLanguageCodes();
        $placeTypes = ['from', 'to'];
        foreach ($placeTypes as $placeType) {
            $connectionsByPlaces = $this->groupConnectionsByPlaces($connections, $placeType);
            foreach ($connectionsByPlaces as $place => $connectionsOfOnePlace) {
                foreach ($languages as $matchLanguage) {
                    $connectionsByLabel = $this->groupConnectionsByLabels($connectionsOfOnePlace, $matchLanguage);
                    foreach ($connectionsByLabel as $connectionsWithSameLabel) {
                        [$matchingLanguages, $differentLanguages] = $this->findMatchingLanguages($languages, $connectionsWithSameLabel);
                        if (count($matchingLanguages) != 0 && count($differentLanguages) != 0) {
                            return [
                                'transitionLabels' => array_values($this->getTransitionLabels($connectionsWithSameLabel)),
                                'matchingLanguages' => array_values($matchingLanguages),
                                'differentLanguages' => array_values($differentLanguages),
                                'placeLabel' => $this->getPlaceLabel($place, $this->places),
                                'direction' => $placeType
                            ];
                        }
                    }
                }
            }
        }
        return null;
    }

    private function splitTransitions(array $transitions): array {
        $split = [];
        foreach ($transitions as $transition) {
            foreach ($transition->getToIds() as $to) {
                foreach ($transition->getFromIds() as $from) {
                    $split[] = [
                        "id" => $transition->getId(),
                        "from" => $from,
                        "to" => $to,
                        "label" => $transition->getLabel(),
                    ];
                }
            }
        }
        return $split;
    }

    private function groupConnectionsByPlaces(array $connections, string $place) {
        $groupedByPlace = ArrayUtils::groupBy(
            $connections,
            function ($connection) use ($place) {
                return $connection[$place];
            }
        );
        return ArrayUtils::removeValuesShorterThan($groupedByPlace, 2);
    }

    private function groupConnectionsByLabels(array $connections, string $language) {
        $sameInLanguage = ArrayUtils::groupBy(
            $connections,
            function ($connection) use ($language) {
                return $connection['label'][$language] ?? "";
            }
        );
        return ArrayUtils::removeValuesShorterThan($sameInLanguage, 2);
    }

    private function findMatchingLanguages(array $languagesToCheck, array $connections) {
        $matchingLanguages = [];
        $differentLanguages = [];
        foreach ($languagesToCheck as $language) {
            if ($this->doAllLabelsMatch($connections, $language)) {
                $matchingLanguages[] = $language;
            } else {
                $differentLanguages[] = $language;
            }
        }
        return [$matchingLanguages, $differentLanguages];
    }

    private function doAllLabelsMatch(array $connections, string $language) {
        return ArrayUtils::allEqual(
            $connections,
            function ($connection) use ($language) {
                return $connection['label'][$language] ?? "";
            }
        );
    }

    private function getTransitionLabels(array $connections) {
        return array_map(
            function ($connection) {
                return ['label' => $connection['label']];
            },
            $connections
        );
    }

    private function getPlaceLabel(string $placeId, array $places) {
        if ($places != null) {
            foreach ($places as $place) {
                if ($place->getId() == $placeId) {
                    return ["label" => $place->getLabel()];
                }
            }
        }
        Assertion::true(false, 'Transition refers to place not given in withPlaces.');
        return null;
    }
}
