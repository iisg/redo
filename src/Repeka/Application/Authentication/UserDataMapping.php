<?php
namespace Repeka\Application\Authentication;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfig;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;

class UserDataMapping {
    use CommandBusAware;

    /** @var ImportConfigFactory */
    private $importConfigFactory;
    private $mappingConfigPath;

    public function __construct(ImportConfigFactory $importConfigFactory, string $mappingConfigPath) {
        $this->importConfigFactory = $importConfigFactory;
        $this->mappingConfigPath = $mappingConfigPath;
    }

    public function mappingExists(): bool {
        return $this->mappingConfigPath && is_readable($this->mappingConfigPath);
    }

    public function getImportConfig(): ImportConfig {
        $userResourceKind = $this->handleCommand(new ResourceKindQuery(SystemResourceKind::USER));
        return $this->importConfigFactory->fromFile($this->mappingConfigPath, $userResourceKind);
    }
}
