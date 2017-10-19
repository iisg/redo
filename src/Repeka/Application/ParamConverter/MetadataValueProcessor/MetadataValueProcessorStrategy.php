<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Repeka\Domain\Entity\MetadataControl;
use Symfony\Component\HttpFoundation\Request;

interface MetadataValueProcessorStrategy {
    public function processValues(array $values, Request $request): array;

    public function getSupportedControl(): MetadataControl;
}
