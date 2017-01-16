<?php
// @codingStandardsIgnoreFile
namespace Repeka\Domain\Validation;

use Repeka\Domain\Entity\ResourceKind;

/**
 * @method static Validator notBlankInAllLanguages(array $languages)
 * @method static Validator containsOnlyAvailableLanguages(array $languages)
 * @method static Validator containsOnlyValuesForMetadataDefinedInResourceKind(ResourceKind $resourceKind)
 */
class Validator extends \Respect\Validation\Validator {
}

Validator::with('Repeka\\Domain\\Validation\\Rules\\');
