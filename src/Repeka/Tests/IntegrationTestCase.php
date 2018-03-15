<?php
namespace Repeka\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class IntegrationTestCase extends FunctionalTestCase {
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
    public function prepareIntegrationTest() {
        $this->container = self::createClient()->getContainer();
        $kernel = $this->container->get('kernel');
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            ini_set('memory_limit', '512M');
            define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
            $this->executeCommand('doctrine:database:drop --force --if-exists');
            $this->executeCommand('doctrine:database:create');
        }
        $this->clearDatabase();
    }

    protected function clearDatabase() {
        $this->executeCommand('doctrine:schema:drop --force');
        $this->executeCommand('doctrine:migrations:version --delete --all');
        $this->executeCommand('repeka:initialize --skip-backup');
    }

    protected function executeCommand(string $command): string {
        $input = new StringInput("$command --env=test");
        $output = new BufferedOutput();
        $input->setInteractive(false);
        $returnCode = $this->application->run($input, $output);
        if ($returnCode != 0) {
            throw new \RuntimeException('Failed to execute command. ' . $output->fetch());
        }
        return $output->fetch();
    }

    protected static function createAuthenticatedClient($username, $password, array $options = [], array $server = []): TestClient {
        $mergedServer = array_merge([
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password,
        ], $server);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::createClient($options, $mergedServer);
    }

    protected static function createAdminClient(array $options = [], array $server = []): TestClient {
        return self::createAuthenticatedClient(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD, $options, $server);
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

    protected function loadAllFixtures(): void {
        $this->executeCommand('doctrine:fixtures:load --append');
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

    protected function handleCommand(Command $command) {
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
        $result = $this->handleCommand(new LanguageCreateCommand($code, $flag, $name));
        $this->container->reset();
        return $result;
    }

    protected function createMetadata(
        string $name,
        array $label,
        array $description = [],
        array $placeholder = [],
        string $control = 'text',
        string $resourceClass = 'books',
        array $constraints = []
    ): Metadata {
        $constraints = array_merge(['maxCount' => 0], $constraints);
        return $this->handleCommand(
            new MetadataCreateCommand($name, $label, $description, $placeholder, $control, $resourceClass, $constraints)
        );
    }

    protected function createResourceKind(
        array $label,
        array $metadataList,
        array $displayStrategies = [],
        ResourceWorkflow $workflow = null
    ): ResourceKind {
        return $this->handleCommand(new ResourceKindCreateCommand($label, $metadataList, $displayStrategies, $workflow));
    }

    protected function createResource(ResourceKind $resourceKind, array $contents): ResourceEntity {
        $user = new UserEntity();
        return $this->handleCommand(new ResourceCreateCommand($resourceKind, ResourceContents::fromArray($contents), $user));
    }

    protected function createWorkflow(array $name, string $resourceClass, array $places, array $transitions): ResourceWorkflow {
        return $this->handleCommand(new ResourceWorkflowCreateCommand($name, $places, $transitions, $resourceClass, null, null));
    }

    protected function simulateAuthentication(UserEntity $user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }
}
