<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\XmlImport\Config\JsonImportConfigLoader;
use Repeka\Domain\XmlImport\Executor\ImportResult;
use Repeka\Domain\XmlImport\Executor\XmlImportExecutor;
use Repeka\Domain\XmlImport\XmlResourceDownloader;

class XmlImportQueryHandler {
    /** @var XmlResourceDownloader */
    private $downloader;
    /** @var JsonImportConfigLoader */
    private $configLoader;
    /** @var XmlImportExecutor */
    private $importExecutor;

    public function __construct(
        XmlResourceDownloader $downloader,
        JsonImportConfigLoader $configLoader,
        XmlImportExecutor $importExecutor
    ) {
        $this->downloader = $downloader;
        $this->configLoader = $configLoader;
        $this->importExecutor = $importExecutor;
    }

    public function handle(XmlImportQuery $query): ImportResult {
        $resourceXml = $this->downloader->downloadById($query->getId());
        if ($resourceXml === null) {
            throw new EntityNotFoundException('xmlResource', $query->getId());
        }
        $config = $this->configLoader->load($query->getConfig(), $query->getResourceKind());
        return $this->importExecutor->execute($resourceXml, $config, $query->getResourceKind());
    }
}
