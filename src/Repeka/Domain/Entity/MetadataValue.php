<?php
namespace Repeka\Domain\Entity;

class MetadataValue {
    private $value;
    private $submetadata;

    public function __construct($metadataSpec) {
        if (is_array($metadataSpec) && array_key_exists('value', $metadataSpec)) {
            $this->value = $metadataSpec['value'];
            $this->submetadata = $metadataSpec['submetadata'] ?? [];
        } else {
            $this->value = $metadataSpec;
            $this->submetadata = [];
        }
    }

    /** @return mixed */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return array of arrays of MetadataValue's
     */
    public function getSubmetadata($metadataOrId = null): array {
        if ($metadataOrId !== null) {
            if ($metadataOrId instanceof Metadata) {
                $metadataOrId = $metadataOrId->getId();
            }
            return $this->mapValuesToSelf($this->submetadata[$metadataOrId] ?? []);
        } else {
            return array_map(
                function (array $values) {
                    return $this->mapValuesToSelf($values);
                },
                $this->submetadata
            );
        }
    }

    public function toArray(): array {
        $array = ['value' => $this->value];
        if ($this->submetadata) {
            $array['submetadata'] = $this->submetadata;
        }
        return $array;
    }

    private function mapValuesToSelf(array $values): array {
        return array_map(
            function ($metadataValue) {
                return new self($metadataValue);
            },
            $values
        );
    }

    public function withNewValue($newValue): MetadataValue {
        $array = $this->toArray();
        $array['value'] = $newValue;
        return new self($array);
    }

    public function __toString(): string {
        return is_array($this->value) ? ($this->value['displayValue'] ?? '?') : strval($this->value);
    }
}
