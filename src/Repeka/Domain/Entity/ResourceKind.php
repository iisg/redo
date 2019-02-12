<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Utils\EntityUtils;

class ResourceKind implements Identifiable, HasResourceClass {
    private $id;
    private $label;
    private $metadataList;
    private $metadataOverrides;
    /** @var ResourceWorkflow */
    private $workflow;
    private $resourceClass;

    /**
     * @param string[] $label
     * @param Metadata[] $metadataList
     */
    public function __construct(array $label, array $metadataList, ResourceWorkflow $workflow = null) {
        $this->setMetadataList($metadataList);
        $this->detectResourceClass();
        $this->label = $label;
        $this->workflow = $workflow;
    }

    private function detectResourceClass() {
        $nonSystemMetadata = array_filter(
            $this->metadataList,
            function (Metadata $metadata) {
                return !!$metadata->getResourceClass();
            }
        );
        Assertion::greaterOrEqualThan(count($nonSystemMetadata), 1, 'Could not detect resource class from system metadata only.');
        $this->resourceClass = current($nonSystemMetadata)->getResourceClass();
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
        return $this->metadataList;
    }

    /** @return array ['group1' => [$m1, $m2], 'group2' => [$m3]] */
    public function getGroupedMetadataList(): array {
        return ArrayUtils::groupBy(
            $this->getMetadataList(),
            function ($metadata) {
                return $metadata->getGroupId();
            }
        );
    }

    public function getMetadataById(int $id): Metadata {
        foreach ($this->getMetadataList() as $metadata) {
            if ($metadata->getId() === $id) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf("Metadata not found for ID #%d in resource kind #%d", $id, $this->getId()));
    }

    public function getMetadataByIdOrName($idOrName): Metadata {
        return is_numeric($idOrName) ? $this->getMetadataById($idOrName) : $this->getMetadataByName($idOrName);
    }

    public function hasMetadata(int $id): bool {
        return in_array($id, $this->getMetadataIds());
    }

    public function getMetadataByName(string $name): Metadata {
        $name = Metadata::normalizeMetadataName($name);
        foreach ($this->getMetadataList() as $metadata) {
            if ($metadata->getName() === $name) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf("Metadata not found for name '%s' in resource kind #%d", $name, $this->getId()));
    }

    /** @return Metadata[] */
    public function getMetadataByControl(MetadataControl $control): array {
        return array_values(
            array_filter(
                $this->getMetadataList(),
                function (Metadata $metadata) use ($control) {
                    return $control->equals($metadata->getControl());
                }
            )
        );
    }

    /** @return Metadata[] */
    public function getDynamicMetadata(): array {
        return array_values(
            array_filter(
                $this->getMetadataList(),
                function (Metadata $metadata) {
                    return $metadata->isDynamic();
                }
            )
        );
    }

    public function update(array $newLabel) {
        $this->label = array_filter($newLabel, 'trim');
    }

    public function getWorkflow(): ?ResourceWorkflow {
        return $this->workflow;
    }

    public function setWorkflow(ResourceWorkflow $workflow) {
        $this->workflow = $workflow;
    }

    /** @return int[] */
    public function getMetadataIds(): array {
        return EntityUtils::mapToIds($this->getMetadataList());
    }

    public function setMetadataList(array $metadataList) {
        Assertion::greaterOrEqualThan(count($metadataList), 1, 'ResourceKind must contain at least one metadata.');
        $this->metadataList = $metadataList;
    }

    public function getMetadataOverrides() {
        return $this->metadataOverrides;
    }

    public function setMetadataOverrides($metadataOverrides) {
        $this->metadataOverrides = $metadataOverrides;
    }
}
