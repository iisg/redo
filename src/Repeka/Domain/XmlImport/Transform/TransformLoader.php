<?php
namespace Repeka\Domain\XmlImport\Transform;

class TransformLoader {
    /** @var RegexReplaceTransform */
    private $regexReplaceTransform;
    /** @var JoinTransform */
    private $joinTransform;

    public function __construct(RegexReplaceTransform $regexReplaceTransform, JoinTransform $joinTransform) {
        $this->regexReplaceTransform = $regexReplaceTransform;
        $this->joinTransform = $joinTransform;
    }

    /**
     * @param array[] $transforms with name string keys
     * @return Transform[] with name string keys
     */
    public function load(array $transforms): array {
        $loaded = [];
        foreach ($transforms as $name => $params) {
            $loaded[$name] = $this->loadTransform($name, $params);
        }
        return $loaded;
    }

    private function loadTransform(string $name, array $params): Transform {
        $paramNames = array_keys($params);
        if ($this->setsAreEqual($paramNames, ['regex', 'replacement'])) {
            return $this->regexReplaceTransform->forArguments($params['regex'], $params['replacement']);
        } elseif ($paramNames == ['glue']) {
            return $this->joinTransform->forArguments($params['glue']);
        } else {
            throw new InvalidTransformException($name, $paramNames);
        }
    }

    private function setsAreEqual(array $set1, array $set2): bool {
        return count($set1) == count($set2) && array_diff($set1, $set2) == [];
    }
}
