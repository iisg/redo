<?php
namespace Repeka\Domain\Factory;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataFactory {
    public function create(MetadataCreateCommand $command) {
        return Metadata::create(
            new MetadataControl($command->getControlName()),
            $command->getName(),
            $command->getLabel(),
            $command->getResourceClass(),
            $command->getPlaceholder(),
            $command->getDescription(),
            $command->getConstraints(),
            $command->isShownInBrief()
        );
    }

    public function createWithParent(array $newChildMetadata, Metadata $parent) {
        $metadata = MetadataFactory::create(MetadataCreateCommand::fromArray($newChildMetadata));
        $metadata->setParent($parent);
        return $metadata;
    }

    public function createWithBaseAndParent(Metadata $base, Metadata $parent, array $newChildMetadata) {
        $metadata = Metadata::createChild($base, $parent);
        $metadata->update(
            $newChildMetadata['label'],
            $newChildMetadata['placeholder'],
            $newChildMetadata['description'],
            $newChildMetadata['constraints'],
            $newChildMetadata['shownInBrief']
        );
        return $metadata;
    }

    public function createForResourceKind(ResourceKind $resourceKind, Metadata $base, Metadata $metadata) {
        Assertion::true($base->isBase(), "Given metadata (ID: {$base->getId()}) cannot be used as base.");
        $constraints = $this->removeUnmodifiedConstraints($metadata->getConstraints(), $base->getConstraints());
        return Metadata::createForResourceKind(
            $metadata->getLabel(),
            $resourceKind,
            $base,
            $metadata->getResourceClass(),
            $metadata->getPlaceholder(),
            $metadata->getDescription(),
            $constraints,
            $metadata->isShownInBrief()
        );
    }

    public function removeUnmodifiedConstraints(array $constraints, array $baseConstraints): array {
        foreach (array_keys($baseConstraints) as $constraintName) {
            if (!array_key_exists($constraintName, $constraints)) {
                continue;
            }
            $baseConstraint = $baseConstraints[$constraintName];
            $concreteConstraint = $constraints[$constraintName];
            array_multisort($baseConstraint);
            array_multisort($concreteConstraint);
            if ($concreteConstraint == $baseConstraint) {
                unset($constraints[$constraintName]);
            }
        }
        return $constraints;
    }
}
