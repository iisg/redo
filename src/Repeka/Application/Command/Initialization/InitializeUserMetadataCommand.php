<?php
namespace Repeka\Application\Command\Initialization;

use Assert\Assertion;
use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\XmlImport\Config\JsonImportConfigLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeUserMetadataCommand extends TransactionalCommand {
    /** @var CommandBus */
    private $commandBus;
    /** @var JsonImportConfigLoader */
    private $configLoader;
    private $mappingConfigPath;
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(
        CommandBus $commandBus,
        JsonImportConfigLoader $configLoader,
        string $mappingConfigPath,
        LanguageRepository $languageRepository
    ) {
        parent::__construct();
        $this->commandBus = $commandBus;
        $this->configLoader = $configLoader;
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
            $jsonConfig = json_decode(file_get_contents($this->mappingConfigPath), true);
            Assertion::notNull($jsonConfig, 'Invalid user data mapping in ' . $this->mappingConfigPath . ': ' . json_last_error_msg());
            $config = $this->configLoader->load($jsonConfig, $userResourceKind);
            $metadataNamesToCreate = $config->getInvalidMetadataKeys();
            if ($metadataNamesToCreate) {
                $metadataList = array_map(function (Metadata $m) {
                    return ['baseId' => $m->getBaseId()];
                }, $userResourceKind->getMetadataList());
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
                        $userResourceKind->getResourceClass(),
                        [
                            'maxCount' => 0,
                            'regex' => '',
                        ]
                    ));
                    $metadataList[] = ['baseId' => $metadata->getId()];
                }
                $this->commandBus->handle(new ResourceKindUpdateCommand(
                    $userResourceKind->getId(),
                    $userResourceKind->getLabel(),
                    $metadataList,
                    $userResourceKind->getDisplayStrategies()
                ));
            }
        }
    }
}
