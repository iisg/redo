<?php
// @codingStandardsIgnoreFile
namespace Repeka\Domain\Validation;

/**
 * @method static Validator notBlankInLanguage(string $language)
 */
class Validator extends \Respect\Validation\Validator {
}

Validator::with('Repeka\\Domain\\Validation\\Rules\\');
