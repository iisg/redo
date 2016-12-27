<?php
namespace Repeka\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Repeka\Domain\Exception\IllegalEntityStateException;

class ResourceKind {
    private $id;
    private $label;
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
    }
}
