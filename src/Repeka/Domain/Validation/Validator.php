<?php
// @codingStandardsIgnoreFile
namespace Repeka\Domain\Validation;

/**
 * @method static Validator notBlankInAllLanguages(array $languages)
 */
class Validator extends \Respect\Validation\Validator {
}

Validator::with('Repeka\\Domain\\Validation\\Rules\\');
