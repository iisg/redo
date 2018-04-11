<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataDeleteCommandValidator extends CommandAttributesValidator {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceKindRepository $resourceKindRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute(
            'metadata',
            Validator::allOf(
                Validator::callback([$this, 'nonSystemMetadata']),
                Validator::callback([$this, 'metadataDoesNotHaveChildren'])->setTemplate('metadata kind has submetadata kinds'),
                Validator::callback([$this, 'metadataIsNotUsedInAnyResourceKind'])
                    ->setTemplate('metadata kind is used in some resource kinds')
            )
        );
    }

    public function nonSystemMetadata(Metadata $metadata): bool {
        return $metadata->getId() > 0;
    }

    public function metadataDoesNotHaveChildren(Metadata $metadata): bool {
        return $this->metadataRepository->countByParent($metadata) === 0;
    }

    public function metadataIsNotUsedInAnyResourceKind(Metadata $metadata): bool {
        return $this->resourceKindRepository->countByMetadata($metadata) === 0;
    }
}
