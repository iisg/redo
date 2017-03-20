<?php
namespace Repeka\Domain\Factory;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataFactory {
    public function create(MetadataCreateCommand $command) {
        return Metadata::create(
            $command->getControl(),
            $command->getName(),
            $command->getLabel(),
            $command->getPlaceholder(),
            $command->getDescription()
        );
    }

    public function createWithParent(array $newChildMetadata, Metadata $parent) {
        $metadata = MetadataFactory::create(MetadataCreateCommand::fromArray($newChildMetadata));
        $metadata->setParent($parent);
        return $metadata;
    }

    public function createForResourceKind(ResourceKind $resourceKind, Metadata $base, Metadata $metadata) {
        Assertion::true($base->isBase(), "Given metadata (ID: {$base->getId()}) cannot be used as base.");
        return Metadata::createForResourceKind(
            $metadata->getLabel(),
            $resourceKind,
            $base,
            $metadata->getPlaceholder(),
            $metadata->getDescription()
        );
    }
}
