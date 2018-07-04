<?php
namespace Repeka\Plugins\Ocr\Model;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;

interface OcrCommunicator {
    public function sendToOcr(array $files, ResourceWorkflowPlacePluginConfiguration $config);
}
