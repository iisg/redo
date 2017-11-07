<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\XmlImport\XmlResourceDownloader;

class XmlImportQueryHandler {
    /** @var XmlResourceDownloader */
    private $downloader;

    public function __construct(XmlResourceDownloader $downloader) {
        $this->downloader = $downloader;
    }

    public function handle(XmlImportQuery $query): string {
        $resource = $this->downloader->downloadById($query->getId());
        if ($resource === null) {
            throw new EntityNotFoundException('xmlResource', $query->getId());
        }
        return $resource;
    }
}
