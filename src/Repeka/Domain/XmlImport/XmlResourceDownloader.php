<?php
namespace Repeka\Domain\XmlImport;

interface XmlResourceDownloader {
    public function downloadById(string $id): ?string;
}
