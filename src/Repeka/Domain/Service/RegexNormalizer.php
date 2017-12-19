<?php
namespace Repeka\Domain\Service;

interface RegexNormalizer {
    public function normalize(string $plainRegex): string;
}
