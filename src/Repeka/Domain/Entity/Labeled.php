<?php
namespace Repeka\Domain\Entity;

interface Labeled {
    public function toArray(): array;

    public function getLabel(): array;
}
