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
        $resourceKind = new ResourceKind($command->getLabel());
        foreach ($command->getMetadataList() as $data) {
            $baseId = $data['base_id'];
            $metadata = $this->metadataFactory->create(MetadataCreateCommand::fromArray($data));
            $base = $this->metadataRepository->findOne($baseId);
            $metadata = $this->metadataFactory->createForResourceKind($resourceKind, $base, $metadata);
            $metadata = $this->metadataRepository->save($metadata);
            $resourceKind->addMetadata($metadata);
        }
        return $resourceKind;
    }
}
