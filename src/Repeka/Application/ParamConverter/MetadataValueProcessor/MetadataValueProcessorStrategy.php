<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Symfony\Component\HttpFoundation\Request;

interface MetadataValueProcessorStrategy {
    public function processValues(array $values, Request $request): array;

    public function getSupportedControl(): string;
}
