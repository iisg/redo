<?php
namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Authentication\UserDataMapping;
use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeUserMetadataCommand extends TransactionalCommand {
    use CommandBusAware;

    /** @var LanguageRepository */
    private $languageRepository;
    /** @var UserDataMapping */
    private $userDataMapping;

    public function __construct(UserDataMapping $userDataMapping, LanguageRepository $languageRepository) {
        parent::__construct();
        $this->languageRepository = $languageRepository;
        $this->userDataMapping = $userDataMapping;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:user-metadata')
            ->setDescription('Inserts user metadata from user data mapping file.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        if ($this->userDataMapping->mappingExists()) {
            /** @var ResourceKind $userResourceKind */
            $userResourceKind = $this->handleCommand(new ResourceKindQuery(SystemResourceKind::USER));
            $config = $this->userDataMapping->getImportConfig();
            $metadataNamesToCreate = $config->getInvalidMetadataKeys();
            if ($metadataNamesToCreate) {
                $metadataList = $userResourceKind->getMetadataList();
                foreach ($metadataNamesToCreate as $metadataNameToCreate) {
                    $label = [];
                    foreach ($this->languageRepository->getAvailableLanguageCodes() as $code) {
                        $label[$code] = $metadataNameToCreate;
                    }
                    $metadata = $this->handleCommand(
                        new MetadataCreateCommand(
                            $metadataNameToCreate,
                            $label,
                            [],
                            [],
                            MetadataControl::TEXT,
                            $userResourceKind->getResourceClass()
                        )
                    );
                    $metadataList[] = $metadata;
                }
                $this->handleCommand(
                    new ResourceKindUpdateCommand(
                        $userResourceKind,
                        $userResourceKind->getLabel(),
                        $metadataList
                    )
                );
                $output->writeln("Created user metadata based on user data mapping: " . count($metadataNamesToCreate));
            }
        }
    }
}
