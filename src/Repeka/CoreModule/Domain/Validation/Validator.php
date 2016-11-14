<?php
// @codingStandardsIgnoreFile
namespace Repeka\CoreModule\Domain\Validation;

/**
 * @method static Validator notBlankInLanguage(string $language)
 */
class Validator extends \Respect\Validation\Validator {
}

Validator::with('Repeka\\DataModule\\Domain\\Validation\\Rules\\');
