<?php
namespace Repeka\Domain\XmlImport\Transform;

interface Transform {
    /**
     * @param string[] $values
     * @return string[]
     */
    public function apply(array $values): array;
}
