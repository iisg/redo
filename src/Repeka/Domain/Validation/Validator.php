<?php
// @codingStandardsIgnoreFile
namespace Repeka\Domain\Validation;

/**
 * @method static Validator notBlankInAllLanguages(array $languages)
 * @method static Validator containsOnlyAvailableLanguages(array $languages)
 */
class Validator extends \Respect\Validation\Validator {
}

Validator::with('Repeka\\Domain\\Validation\\Rules\\');
