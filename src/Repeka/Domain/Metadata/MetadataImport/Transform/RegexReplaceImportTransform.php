<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Service\RegexNormalizer;

class RegexReplaceImportTransform extends AbstractImportTransform {
    /** @var RegexNormalizer */
    private $regexNormalizer;

    public function __construct(RegexNormalizer $regexNormalizer) {
        $this->regexNormalizer = $regexNormalizer;
    }

    public function apply(array $values, array $config): array {
        $regex = $config['regex'] ?? null;
        Assertion::notNull($regex, 'regexReplace transform require regex to be configured.');
        $regex = $this->regexNormalizer->normalize($regex);
        $replacement = $config['replacement'] ?? '';
        return array_values(
            array_map(
                function ($value) use ($replacement, $regex) {
                    return preg_replace($regex, $replacement, $value);
                },
                $values
            )
        );
    }
}
