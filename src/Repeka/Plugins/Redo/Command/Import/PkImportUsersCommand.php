<?php
namespace Repeka\Plugins\Redo\Command\Import;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\User;
use Repeka\Domain\EventListener\UpdateDependentDisplayStrategiesListener;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Plugins\Redo\Authentication\PkSoapAuthenticator;
use Repeka\Plugins\Redo\Command\Import\XmlExtractStrategy\PkResourcesDumpXmlExtractor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.NPathComplexity")
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class PkImportUsersCommand extends ContainerAwareCommand {
    use CommandBusAware;

    const ID_MAPPING_FILE = \AppKernel::VAR_PATH . '/import/id-mapping.json';

    const IMPORTED = 'imported';
    const UPDATED = 'updated';

    /** @var ImportConfigFactory */
    private $importConfigFactory;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var UserProviderInterface */
    private $userRepository;

    public function __construct(
        ImportConfigFactory $importConfigFactory,
        ResourceKindRepository $resourceKindRepository,
        ResourceRepository $resourceRepository,
        UserRepository $userRepository
    ) {
        $this->importConfigFactory = $importConfigFactory;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function configure() {
        $this
            ->setName('redo:pk-import:import-users')
            ->addArgument('input', InputArgument::REQUIRED)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED)
            ->setDescription('Imports users from given file (without data).');
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    protected function execute(InputInterface $input, OutputInterface $output) {
        UpdateDependentDisplayStrategiesListener::$alwaysLeaveDirty = true;
        $stats = [
            'resources' => 0,
            'imported' => 0,
            'existing' => 0,
        ];
        $idMapping = PkImportResourcesCommand::getIdMapping();
        $xmlFileName = $input->getArgument('input');
        try {
            $xml = PkImportFileLoader::load($xmlFileName);
            $resourceContentsFetcher = new PkResourcesDumpXmlExtractor();
            $users = $resourceContentsFetcher->extractAllResources($xml);
            $idMappingNamespace = 'users';
            if (!isset($idMapping[$idMappingNamespace])) {
                $idMapping[$idMappingNamespace] = [];
            }
            $output->writeln('Importing users');
            $progress = new ProgressBar($output, count($users));
            $progress->display();
            $stats['resources'] = count($users);
            $offset = $input->getOption('offset') ?? 0;
            $limit = $offset + ($input->getOption('limit') ?? $stats['resources']);
            $iteration = 0;
            foreach ($users as $user) {
                $iteration++;
                $progress->advance();
                if ($iteration < $offset) {
                    continue;
                } elseif ($iteration > $limit) {
                    break;
                }
                $userData = $resourceContentsFetcher->extractResourceData($user);
                Assertion::keyExists($userData, 'ID');
                Assertion::keyExists($userData, 'LOGIN');
                if (!isset($idMapping[$idMappingNamespace][$userData['ID']])) {
                    /** @var User $existingUser */
                    $username = PkSoapAuthenticator::normalizeUsername($userData['LOGIN']);
                    $existingUser = $this->userRepository->loadUserByUsername($username);
                    if ($existingUser) {
                        $stats['existing']++;
                    } else {
                        try {
                            $existingUser = FirewallMiddleware::bypass(
                                function () use ($username) {
                                    return $this->commandBus->handle(new UserCreateCommand($username));
                                }
                            );
                        } catch (\Exception $e) {
                            echo PHP_EOL . 'Invalid username: ' . $userData['LOGIN'] . PHP_EOL;
                            throw $e;
                        }
                        $stats['imported']++;
                    }
                    $idMapping[$idMappingNamespace][$userData['ID']] = $existingUser->getUserData()->getId();
                } else {
                    $stats['existing']++;
                }
            }
            $progress->clear();
        } catch (\Exception $e) {
            if (isset($progress)) {
                $progress->clear();
            }
            $error = $e->getMessage();
        }
        file_put_contents(self::ID_MAPPING_FILE, json_encode($idMapping));
        (new Table($output))
            ->setHeaders(['Total users', 'Successfully imported', 'Already existing'])
            ->addRows([$stats])
            ->render();
        $output->writeln(
            "Identifiers of the imported resources has been saved to:\n<info>" . realpath(self::ID_MAPPING_FILE) . "</info>\n" .
            "Keep this file untouched if you want to repeat the import process or map relations in the future."
        );
        if (isset($error)) {
            $output->writeln('<error>IMPORT HAS NOT BEEN FINISHED DUE TO AN ERROR:</error>');
            $output->writeln('<error>' . $error . '</error>');
            return 1;
        }
    }
}
