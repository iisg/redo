<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceKindCreateCommandAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var UnknownLanguageStripper */
    protected $unknownLanguageStripper;

    public function __construct(MetadataRepository $metadataRepository, UnknownLanguageStripper $unknownLanguageStripper) {
        $this->metadataRepository = $metadataRepository;
        $this->unknownLanguageStripper = $unknownLanguageStripper;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @return ResourceKindCreateCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceKindCreateCommand(
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->fetchMetadataIfRequired($command->getMetadataList()),
            $command->getDisplayStrategies(),
            $command->getWorkflow()
        );
    }

    protected function fetchMetadataIfRequired($metadataOrOverrideList): array {
        $metadataList = [];
        foreach ($metadataOrOverrideList as $metadataOrOverride) {
            if ($metadataOrOverride instanceof Metadata) {
                $metadataList[$metadataOrOverride->getId()] = $metadataOrOverride;
            } else {
                Assertion::isArray($metadataOrOverride, 'Invalid metadata override format.');
                Assertion::keyExists($metadataOrOverride, 'id', 'Metadata id is requried when using override format.');
                $id = (int)$metadataOrOverride['id'];
                $metadata = $this->metadataRepository->findOne($id);
                $metadata->updateOverrides($metadataOrOverride);
                $metadataList[$id] = $metadata;
            }
        }
        if (!isset($metadataList[SystemMetadata::PARENT])) {
            $metadataList[SystemMetadata::PARENT] = $this->metadataRepository->findOne(SystemMetadata::PARENT);
        }
        return array_values($metadataList);
    }
}
