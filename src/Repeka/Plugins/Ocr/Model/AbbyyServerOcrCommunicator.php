<?php
namespace Repeka\Plugins\Ocr\Model;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;

class AbbyyServerOcrCommunicator implements OcrCommunicator {
    public function sendToOcr(array $files, ResourceWorkflowPlacePluginConfiguration $config) {
        // to be implemented when we have appropriate ABBYY communication
    }
}
