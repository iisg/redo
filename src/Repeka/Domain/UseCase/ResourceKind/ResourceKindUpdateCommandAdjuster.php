<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Assert\Assertion;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceKindUpdateCommandAdjuster extends ResourceKindCreateCommandAdjuster {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(
        MetadataRepository $metadataRepository,
        UnknownLanguageStripper $unknownLanguageStripper,
        ResourceKindRepository $resourceKindRepository
    ) {
        parent::__construct($metadataRepository, $unknownLanguageStripper);
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @param ResourceKindUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new ResourceKindUpdateCommand(
            $this->findResourceKind($command->getResourceKind()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->fetchMetadataIfRequired($command->getMetadataList()),
            $command->getDisplayStrategies()
        );
    }

    private function findResourceKind($resourceKindOrId) {
        if ($resourceKindOrId instanceof ResourceKind) {
            return $resourceKindOrId;
        } else {
            Assertion::numeric($resourceKindOrId);
            return $this->resourceKindRepository->findOne($resourceKindOrId);
        }
    }
}
