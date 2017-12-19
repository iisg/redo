<?php
namespace Repeka\Domain\XmlImport\Transform;

use Repeka\Domain\Service\RegexNormalizer;

class RegexReplaceTransform implements Transform {
    /** @var RegexNormalizer */
    private $regexNormalizer;

    /** @var string */
    private $regex = null;
    /** @var string */
    private $replacement = null;

    public function __construct(RegexNormalizer $regexNormalizer) {
        $this->regexNormalizer = $regexNormalizer;
    }

    public function forArguments(string $regex, string $replacement): RegexReplaceTransform {
        $instance = new RegexReplaceTransform($this->regexNormalizer);
        $instance->regex = $this->regexNormalizer->normalize($regex);
        $instance->replacement = $replacement;
        return $instance;
    }

    public function apply(array $values): array {
        if ($this->regex === null || $this->replacement === null) {
            throw new \InvalidArgumentException("Replacement not configured. Use configure() first.");
        }
        return array_values(array_map(function ($value) {
            return preg_replace($this->regex, $this->replacement, $value);
        }, $values));
    }
}
