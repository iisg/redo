<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;

class ResourceKindFactory {
    /** @var MetadataFactory */
    private $metadataFactory;

    /** @var  MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataFactory $metadataFactory, MetadataRepository $metadataRepository) {
        $this->metadataFactory = $metadataFactory;
        $this->metadataRepository = $metadataRepository;
    }

    public function create(ResourceKindCreateCommand $command) {
        $resourceKind = new ResourceKind($command->getLabel(), $command->getWorkflow());
        $metadataList = $this->createMetadataList($resourceKind, $command->getMetadataList());
        foreach ($metadataList as $metadata) {
            $resourceKind->addMetadata($metadata);
        }
        return $resourceKind;
    }

    public function createMetadataList(ResourceKind $resourceKind, array $metadataData): array {
        $metadataList = [];
        foreach ($metadataData as $data) {
            $baseId = $data['baseId'];
            $metadata = $this->metadataFactory->create(MetadataCreateCommand::fromArray($data));
            $base = $this->metadataRepository->findOne($baseId);
            $metadata = $this->metadataFactory->createForResourceKind($resourceKind, $base, $metadata);
            $metadata->updateOrdinalNumber(count($metadataList));
            $metadataList[] = $metadata;
        }
        return $metadataList;
    }
}
