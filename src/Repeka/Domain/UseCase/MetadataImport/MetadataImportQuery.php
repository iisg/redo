<?php
namespace Repeka\Domain\UseCase\MetadataImport;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\MetadataImport\Config\ImportConfig;

class MetadataImportQuery extends AbstractCommand implements NonValidatedCommand {
    private $data;
    /** @var ImportConfig */
    private $config;

    public function __construct(array $data, ImportConfig $config) {
        $this->data = $data;
        $this->config = $config;
    }

    public function getData(): array {
        return $this->data;
    }

    public function getConfig(): ImportConfig {
        return $this->config;
    }
}
