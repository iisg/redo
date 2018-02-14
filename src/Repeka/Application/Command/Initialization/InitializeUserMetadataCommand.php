<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeUserMetadataCommand extends TransactionalCommand {
    /** @var CommandBus */
    private $commandBus;
    /** @var ImportConfigFactory */
    private $configFactory;
    private $mappingConfigPath;
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(
        CommandBus $commandBus,
        ImportConfigFactory $configFactory,
        string $mappingConfigPath,
        LanguageRepository $languageRepository
    ) {
        parent::__construct();
        $this->commandBus = $commandBus;
        $this->configFactory = $configFactory;
        $this->mappingConfigPath = $mappingConfigPath;
        $this->languageRepository = $languageRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:user-metadata')
            ->setDescription('Inserts user metadata from user data mapping file.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        if ($this->mappingConfigPath && is_readable($this->mappingConfigPath)) {
            /** @var ResourceKind $userResourceKind */
            $userResourceKind = $this->commandBus->handle(new ResourceKindQuery(SystemResourceKind::USER));
            $config = $this->configFactory->fromFile($this->mappingConfigPath, $userResourceKind);
            $metadataNamesToCreate = $config->getInvalidMetadataKeys();
            if ($metadataNamesToCreate) {
                $metadataList = $userResourceKind->getMetadataList();
                foreach ($metadataNamesToCreate as $metadataNameToCreate) {
                    $label = [];
                    foreach ($this->languageRepository->getAvailableLanguageCodes() as $code) {
                        $label[$code] = $metadataNameToCreate;
                    }
                    $metadata = $this->commandBus->handle(new MetadataCreateCommand(
                        $metadataNameToCreate,
                        $label,
                        [],
                        [],
                        MetadataControl::TEXT,
                        $userResourceKind->getResourceClass()
                    ));
                    $metadataList[] = $metadata;
                }
                $this->commandBus->handle(new ResourceKindUpdateCommand(
                    $userResourceKind,
                    $userResourceKind->getLabel(),
                    $metadataList,
                    $userResourceKind->getDisplayStrategies()
                ));
                $output->writeln("Created user metadata based on user data mapping: " . count($metadataNamesToCreate));
            }
        }
    }
}
