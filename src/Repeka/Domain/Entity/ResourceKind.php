<?php
namespace Repeka\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Repeka\Domain\Exception\IllegalEntityStateException;

class ResourceKind {
    private $id;
    private $label;
    /** @var ArrayCollection|Metadata[] */
    private $metadataList;

    public function __construct(array $label) {
        $this->label = $label;
        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#collections
        $this->metadataList = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /**
     * @return Metadata[]
     */
    public function getMetadataList(): array {
        return $this->metadataList->toArray();
    }

    public function addMetadata(Metadata $metadata) {
        if (in_array($metadata, $this->getMetadataList())) {
            throw new IllegalEntityStateException('You cannot add the same metadata twice.');
        }
        $this->metadataList[] = $metadata;
        $this->sortMetadataByOrdinalNumber();
    }

    /**
     * @param string[] $newLabel
     * @param Metadata[] $newMetadataList
     */
    public function update(array $newLabel, array $newMetadataList) {
        $this->label = array_merge($this->label, array_filter($newLabel, 'trim'));
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
                $currentMetadata[$newMetadata->getBaseId()]
                    ->update($newMetadata->getLabel(), $newMetadata->getPlaceholder(), $newMetadata->getDescription());
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
}
