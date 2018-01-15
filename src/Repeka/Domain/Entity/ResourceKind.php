<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\MetadataAlreadyPresentException;

class ResourceKind implements Identifiable {
    private $id;
    private $label;
    /** @var ArrayCollection|Metadata[] */
    private $metadataList;
    /** @var ResourceWorkflow */
    private $workflow;
    private $resourceClass;
    private $displayStrategies = [];

    public function __construct(array $label, string $resourceClass, array $displayStrategies = [], ResourceWorkflow $workflow = null) {
        $this->label = $label;
        $this->metadataList = new ArrayCollection();
        $this->resourceClass = $resourceClass;
        $this->setDisplayStrategies($displayStrategies);
        $this->workflow = $workflow;
    }

    public function getId() {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    /** @return Metadata[] */
    public function getMetadataList(): array {
        return $this->metadataList->toArray();
    }

    public function getMetadataById(int $id): Metadata {
        if ($id == SystemMetadata::PARENT) {
            return SystemMetadata::PARENT()->toMetadata();
        }
        foreach ($this->getMetadataList() as $metadata) {
            if ($metadata->getId() === $id) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf("Metadata not found for ID #%d in resource kind #%d", $id, $this->getId()));
    }

    public function getDisplayStrategies(): array {
        return $this->displayStrategies;
    }

    public function getMetadataByBaseId(int $baseId): Metadata {
        if ($baseId == SystemMetadata::PARENT) {
            return SystemMetadata::PARENT()->toMetadata();
        }
        foreach ($this->getMetadataList() as $metadata) {
            if ($metadata->getBaseId() === $baseId) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf(
            "Metadata not found for base metadata #%d in resource kind #%d",
            $baseId,
            $this->getId()
        ));
    }

    public function getMetadataByName(string $name): Metadata {
        foreach ($this->getMetadataList() as $metadata) {
            if ($metadata->getName() === $name) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf("Metadata not found for name '%s' in resource kind #%d", $name, $this->getId()));
    }

    /** @return Metadata[] */
    public function getMetadataByControl(MetadataControl $control): array {
        return array_values(array_filter($this->getMetadataList(), function (Metadata $metadata) use ($control) {
            return $control->equals($metadata->getControl());
        }));
    }

    public function addMetadata(Metadata $metadata) {
        Assertion::notNull($metadata->getBaseId());
        if (in_array($metadata->getBaseId(), $this->getBaseMetadataIds())) {
            throw new MetadataAlreadyPresentException($this, $metadata);
        }
        $this->metadataList[] = $metadata;
        $this->sortMetadataByOrdinalNumber();
    }

    /**
     * @param string[] $newLabel
     * @param Metadata[] $newMetadataList
     */
    public function update(array $newLabel, array $newMetadataList, array $newDisplayStrategies) {
        $this->label = array_filter($newLabel, 'trim');
        $this->setDisplayStrategies($newDisplayStrategies);
        /** @var Metadata[] $currentMetadata */
        $currentMetadata = [];
        foreach ($this->metadataList as $metadata) {
            $currentMetadata[$metadata->getBaseId()] = $metadata;
        }
        $currentMetadataIds = array_keys($currentMetadata);
        $newMetadataIds = array_map(function (Metadata $metadata) {
            return $metadata->getBaseId();
        }, $newMetadataList);
        foreach ($newMetadataList as $newMetadata) {
            if (in_array($newMetadata->getBaseId(), $currentMetadataIds)) {
                $currentMetadata[$newMetadata->getBaseId()]->update(
                    $newMetadata->getLabel(),
                    $newMetadata->getPlaceholder(),
                    $newMetadata->getDescription(),
                    $newMetadata->getConstraints(),
                    $newMetadata->isShownInBrief()
                );
                $currentMetadata[$newMetadata->getBaseId()]
                    ->updateOrdinalNumber($newMetadata->getOrdinalNumber());
            } else {
                $this->addMetadata($newMetadata);
            }
        }
        foreach ($currentMetadataIds as $currentMetadataId) {
            if (!in_array($currentMetadataId, $newMetadataIds)) {
                $this->metadataList->removeElement($currentMetadata[$currentMetadataId]);
            }
        }
        $this->sortMetadataByOrdinalNumber();
    }

    private function sortMetadataByOrdinalNumber() {
        $metadataList = $this->getMetadataList();
        usort($metadataList, [Metadata::class, 'compareOrdinalNumbers']);
        // do not replace it with new ArrayCollection($metadataList) as it will make Doctrine think all current metadata are orphaned!
        $this->metadataList->clear();
        foreach ($metadataList as $metadata) {
            $this->metadataList->add($metadata);
        }
    }

    public function getWorkflow(): ?ResourceWorkflow {
        return $this->workflow;
    }

    /** @return int[] */
    public function getBaseMetadataIds(): array {
        return array_map(function (Metadata $metadata) {
            return $metadata->getBaseId();
        }, $this->getMetadataList());
    }

    private function setDisplayStrategies(array $displayStrategies) {
        $this->displayStrategies = array_filter(array_map('trim', $displayStrategies));
    }
}
