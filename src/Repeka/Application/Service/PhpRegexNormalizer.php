<?php
namespace Repeka\Application\Service;

use Repeka\Domain\Service\RegexNormalizer;

class PhpRegexNormalizer implements RegexNormalizer {
    /**
     * Transforms a plain regular expression string into one that is usable with PHP preg_* functions
     */
    public function normalize(string $plainRegex): string {
        $escaped = preg_replace('#(?<!\\\\)/#', '\\/', $plainRegex);
        return "/$escaped/";
    }
}
