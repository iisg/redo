<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Service\RegexNormalizer;

class RegexConstraint extends AbstractMetadataConstraint {
    /** @var RegexNormalizer */
    private $regexNormalizer;

    public function __construct(RegexNormalizer $regexNormalizer) {
        $this->regexNormalizer = $regexNormalizer;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::TEXT];
    }

    /**
     * @param string $pattern
     * @see https://stackoverflow.com/a/12941133/1937994 for explanation how this regex validation works
     */
    public function isConfigValid($pattern): bool {
        if ($pattern === '') {
            return true;
        } elseif (!is_string($pattern)) {
            return false;
        }
        $phpRegex = $this->regexNormalizer->normalize($pattern);
        return @preg_match($phpRegex, null) !== false;
    }

    /**
     * @param string $pattern
     * @param string[] $input
     */
    public function isValueValid($pattern, $input): bool {
        if ($pattern === '') {
            return true;
        }
        $phpRegex = $this->regexNormalizer->normalize($pattern);
        foreach ($input as $value) {
            if (!preg_match($phpRegex, $value)) {
                return false;
            }
        }
        return true;
    }
}
