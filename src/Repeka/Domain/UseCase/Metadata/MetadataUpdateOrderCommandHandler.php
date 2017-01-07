<?php
namespace Repeka\Domain\UseCase\Metadata;

use Assert\Assertion;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataUpdateOrderCommandHandler {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function handle(MetadataUpdateOrderCommand $command) {
        $metadataList = $this->metadataRepository->findAllBase();
        foreach ($metadataList as $metadata) {
            $ordinal = array_search($metadata->getId(), $command->getMetadataIdsInOrder());
            Assertion::integer($ordinal, "Could not find ordinal number for metadata #{$metadata->getId()}");
            $metadata->updateOrdinalNumber($ordinal);
            $this->metadataRepository->save($metadata);
        }
    }
}
