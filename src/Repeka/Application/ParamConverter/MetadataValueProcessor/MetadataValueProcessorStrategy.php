<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Repeka\Domain\Entity\MetadataControl;
use Symfony\Component\HttpFoundation\Request;

interface MetadataValueProcessorStrategy {
    public function processValue($value, Request $request);

    public function getSupportedControl(): MetadataControl;
}
