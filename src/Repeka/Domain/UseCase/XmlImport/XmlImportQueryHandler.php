<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\XmlImport\Config\JsonImportConfigLoader;
use Repeka\Domain\XmlImport\Executor\ImportResult;
use Repeka\Domain\XmlImport\Executor\XmlImportExecutor;

class XmlImportQueryHandler {
    /** @var JsonImportConfigLoader */
    private $configLoader;
    /** @var XmlImportExecutor */
    private $importExecutor;

    public function __construct(JsonImportConfigLoader $configLoader, XmlImportExecutor $importExecutor) {
        $this->configLoader = $configLoader;
        $this->importExecutor = $importExecutor;
    }

    public function handle(XmlImportQuery $query): ImportResult {
        $config = $this->configLoader->load($query->getConfig(), $query->getResourceKind());
        return $this->importExecutor->execute($query->getXml(), $config, $query->getResourceKind());
    }
}
