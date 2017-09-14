<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataDeleteCommandValidator extends CommandAttributesValidator {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute(
            'metadata',
            Validator::allOf(
                Validator::callback([$this, 'metadataDoesNotHaveChildren'])->setTemplate('metadata kind has submetadata kinds'),
                Validator::callback([$this, 'metadataIsNotUsedInAnyResourceKind'])
                    ->setTemplate('metadata kind is used in some resource kinds')
            )
        );
    }

    public function metadataDoesNotHaveChildren(Metadata $metadata): bool {
        return $this->metadataRepository->countByParent($metadata) === 0;
    }

    public function metadataIsNotUsedInAnyResourceKind(Metadata $metadata): bool {
        return $this->metadataRepository->countByBase($metadata) === 0;
    }
}
