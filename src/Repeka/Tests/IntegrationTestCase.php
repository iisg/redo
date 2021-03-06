<?php
namespace Repeka\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ReflectionClass;
use ReflectionProperty;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\DeveloperBundle\DataFixtures\Redo\AdminAccountFixture;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class IntegrationTestCase extends FunctionalTestCase {
    private static $dataForTests = [];

    /** @var ResettableContainerInterface */
    protected $container;

    /** @var Application */
    private $application;

    protected function setUp() {
        self::loadFixture(new AdminAccountFixture());
    }

    /**
     * PHPUnit listener will call this before each test.
     * It's better than setUp() because we don't have to remember to call this when overriding setUp().
     */
    final public function prepareIntegrationTest() {
        ini_set('memory_limit', '2G');
        $this->setTestContainer(self::createClient());
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:drop --force --if-exists');
            $this->executeCommand('doctrine:database:create');
        }
        self::$dataForTests = array_intersect_key(self::$dataForTests, [static::class => '']);
        $this->clearDatabase();
    }

    protected function clearDatabase() {
        $initializedAtLeastOnce = isset(self::$dataForTests[static::class]);
        if (!$initializedAtLeastOnce || $this->isLarge() || (!$this->hasDependencies() && !$this->isSmall())) {
            $this->executeCommand('doctrine:schema:drop --force');
            $this->executeCommand('doctrine:migrations:version --delete --all');
            $this->executeCommand('repeka:initialize --skip-backup');
            $this->initializeDatabaseForTests();
            $reflection = new ReflectionClass($this);
            $vars = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
            $testState = [];
            foreach ($vars as $var) {
                $var->setAccessible(true);
                $testState[$var->getName()] = $var->getValue($this);
            }
            self::$dataForTests[static::class] = $testState;
        }
        if (isset(self::$dataForTests[static::class])) {
            foreach (self::$dataForTests[static::class] as $fieldName => $value) {
                EntityUtils::forceSetField($this, $value, $fieldName);
            }
        }
    }

    /** @before */
    public function refreshAllEntities() {
        if (isset(self::$dataForTests[static::class])) {
            foreach (self::$dataForTests[static::class] as $fieldName => $value) {
                if ($value instanceof Identifiable) {
                    EntityUtils::forceSetField($this, $this->freshEntity($value), $fieldName);
                }
            }
        }
    }

    protected function freshEntity(Identifiable $entity): Identifiable {
        try {
            $entity = $this->getEntityManager()->find(get_class($entity), $entity->getId());
            $this->getEntityManager()->refresh($entity);
            return $entity;
        } catch (MappingException $e) {
            // we hit Identifiable that is not a persisted entity
            return $entity;
        }
    }

    protected function initializeDatabaseForTests() {
    }

    protected function executeCommand(string $command): string {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);
        try {
            $returnCode = $this->application->run($input, $output);
            if ($returnCode != 0) {
                $this->fail('Failed to execute command. ' . $output->fetch());
            }
        } catch (\Exception $e) {
            $this->fail("Failed to execute command: $command - " . $e->getMessage());
        }
        return $output->fetch();
    }

    protected static function createAuthenticatedClient($username, $password, array $options = [], array $server = []): TestClient {
        $mergedServer = array_merge(
            [
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $password,
            ],
            $server
        );
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::createClient($options, $mergedServer);
    }

    protected static function createAdminClient(array $options = [], array $server = []): TestClient {
        return self::createAuthenticatedClient(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD, $options, $server);
    }

    protected function setTestContainer(TestClient $client) {
        $this->container = $client->getContainer();
        $kernel = $this->container->get('kernel');
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
    }

    /** @param string[] $parts */
    protected static function joinUrl(...$parts): string {
        $url = array_shift($parts);
        while (count($parts) > 0) {
            if ($url[-1] !== '/') {
                $url .= '/';
            }
            $url .= array_shift($parts);
        }
        return $url;
    }

    /** @param int|string|object $entity */
    protected static function oneEntityEndpoint($entity): string {
        return self::joinUrl(static::ENDPOINT, is_object($entity) ? $entity->getId() : $entity);
    }

    protected function getEntityManager(): EntityManagerInterface {
        return $this->container->get(EntityManagerInterface::class);
    }

    protected function loadFixture(FixtureInterface... $fixtures) {
        $executor = new ORMExecutor($this->getEntityManager());
        $loader = new ContainerAwareLoader($this->container);
        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }
        $executor->execute($loader->getFixtures(), true);
    }

    protected function loadAllFixtures($group = 'redo'): void {
        $this->executeCommand('doctrine:fixtures:load --append --group=' . $group);
        $this->executeCommand('doctrine:cache:clear-result');
    }

    protected function persistAndFlush($entities) {
        if (!is_array($entities)) {
            $entities = [$entities];
        }
        $em = $this->getEntityManager();
        foreach ($entities as $entity) {
            $em->persist($entity);
        }
        $em->flush();
    }

    protected function handleCommandBypassingFirewall(Command $command) {
        return FirewallMiddleware::bypass(
            function () use ($command) {
                $commandBus = $this->container->get(CommandBus::class);
                return $commandBus->handle($command);
            }
        );
    }

    protected function handleCommandAs(User $executor, Command $command) {
        EntityUtils::forceSetField($command, $executor, 'executor');
        $commandBus = $this->container->get(CommandBus::class);
        return $commandBus->handle($command);
    }

    protected function clearDefaultLanguages() {
        $languageRepository = $this->container->get('doctrine')->getRepository('RepekaDomain:Language');
        foreach ($languageRepository->findAll() as $language) {
            $this->getEntityManager()->remove($language);
        }
        $this->getEntityManager()->flush();
    }

    protected function createLanguage(string $code, string $flag, string $name): Language {
        return $this->handleCommandBypassingFirewall(new LanguageCreateCommand($code, $flag, $name));
    }

    protected function createMetadata(
        string $name,
        array $label = [],
        array $description = [],
        array $placeholder = [],
        string $control = 'text',
        string $resourceClass = 'books',
        array $constraints = [],
        string $groupId = '',
        Metadata $parent = null
    ): Metadata {
        if (empty($label)) {
            $label = ['PL' => $name, 'EN' => $name];
        }
        return $this->handleCommandBypassingFirewall(
            new MetadataCreateCommand(
                $name,
                $label,
                $description,
                $placeholder,
                $control,
                $resourceClass,
                $constraints,
                $groupId,
                null,
                false,
                false,
                $parent
            )
        );
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    protected function createResourceKind(
        string $name,
        array $label,
        array $metadataList,
        bool $allowedToClone = false,
        ResourceWorkflow $workflow = null
    ): ResourceKind {
        return $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand($name, $label, $metadataList, $allowedToClone, $workflow)
        );
    }

    protected function createResource(ResourceKind $resourceKind, array $contents): ResourceEntity {
        return $this->handleCommandBypassingFirewall(new ResourceCreateCommand($resourceKind, ResourceContents::fromArray($contents)));
    }

    protected function createWorkflow(array $name, string $resourceClass, array $places, array $transitions): ResourceWorkflow {
        return $this->handleCommandBypassingFirewall(
            new ResourceWorkflowCreateCommand($name, $places, $transitions, $resourceClass, null, null)
        );
    }

    protected function simulateAuthentication(User $user): TokenInterface {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        return $token;
    }

    /**
     * Clears the cache of the EntityManager so it sees changes introduced by native SQL queries.
     * @param EntityRepository[]|string[] ...$repositoriesToReset
     */
    protected function resetEntityManager(...$repositoriesToReset) {
        $this->getEntityManager()->flush();
        $this->container->get('doctrine')->resetManager();
        foreach ($repositoriesToReset as $repository) {
            if (is_string($repository)) {
                $repository = $this->container->get($repository);
            }
            EntityUtils::forceSetField($repository, $this->getEntityManager(), '_em');
        }
    }
}
