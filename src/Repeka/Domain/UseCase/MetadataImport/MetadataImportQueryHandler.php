<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Metadata\MetadataImport\ImportResult;
use Repeka\Domain\Metadata\MetadataImport\MetadataImporter;

class MetadataImportQueryHandler {
    /** @var MetadataImporter */
    private $dataImporter;

    public function __construct(MetadataImporter $dataImporter) {
        $this->dataImporter = $dataImporter;
    }

    public function handle(MetadataImportQuery $query): ImportResult {
        return $this->dataImporter->import($query->getData(), $query->getConfig());
    }
}
