<?php
namespace Repeka\Plugins\Ocr\Model;

interface OcrCommunicator {
    public function sendToOcr(array $files);
}
