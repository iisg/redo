<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Service\RegexNormalizer;
use Respect\Validation\Validator;

class RegexConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {
    /** @var RegexNormalizer */
    private $regexNormalizer;

    public function __construct(RegexNormalizer $regexNormalizer) {
        $this->regexNormalizer = $regexNormalizer;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::TEXT, MetadataControl::TEXTAREA];
    }

    /**
     * @param string $pattern
     * @see https://stackoverflow.com/a/12941133/1937994 for explanation how this regex validation works
     * @return bool
     */
    public function isConfigValid($pattern): bool {
        if ($pattern === '') {
            return true;
        } elseif (!is_string($pattern)) {
            return false;
        }
        $phpRegex = $this->regexNormalizer->normalize($pattern);
        $regexValid = @preg_match($phpRegex, null) !== false;
        if ($regexValid) {
            return true;
        } else {
            throw new InvalidCommandException(
                null,
                [
                    'field' => 'constraints',
                    'constraintError' => 'invalidRegex',
                    DomainException::TRANSLATE_PARAMS => ['constraintError'],
                ],
                'invalidRegex'
            );
        }
    }

    public function getValidator(Metadata $metadata, $metadataValue) {
        if ($metadataValue) {
            $pattern = $metadata->getConstraints()[$this->getConstraintName()] ?? null;
            if ($pattern) {
                $phpRegex = $this->regexNormalizer->normalize($pattern) . 's'; // s - multiline modifier
                return Validator::regex($phpRegex);
            }
        }
    }
}
