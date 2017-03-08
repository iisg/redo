<?php
namespace Repeka\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
abstract class IntegrationTestCase extends FunctionalTestCase {
    /** @var ContainerInterface */
    protected $container;

    public function setUp() {
        self::loadFixture(new AdminAccountFixture());
    }

    /**
     * PHPUnit listener will call this before each test.
     * It's better than setUp() because we don't have to remember to call this when overriding setUp().
     */
    public function prepareIntegrationTest() {
        if (!defined('INTEGRATION_TESTS_BOOTSTRAPPED')) {
            $this->lateBootstrapIntegrationTests();
        }
        $this->container = self::createClient()->getContainer();
        $this->purgeDatabase();
    }

    private function purgeDatabase() {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }

    private function lateBootstrapIntegrationTests() {
        include_once(__DIR__ . '/../../../app/integration_tests_bootstrapper.php');
    }

    protected static function createAuthenticatedClient($username, $password, array $options = [], array $server = []): TestClient {
        $mergedServer = array_merge([
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password
        ], $server);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::createClient($options, $mergedServer);
    }

    protected static function createAdminClient(array $options = [], array $server = []): TestClient {
        return self::createAuthenticatedClient(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD, $options, $server);
    }

    protected function getEntityManager() {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    protected function loadFixture(FixtureInterface $fixture) {
        $executor = new ORMExecutor($this->getEntityManager());
        $loader = new ContainerAwareLoader($this->container);
        $loader->addFixture($fixture);
        $executor->execute($loader->getFixtures(), true);
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
        $commandBus = $this->container->get('repeka.command_bus');
        return $commandBus->handle($command);
    }

    protected function createLanguage(string $code, string $flag, string $name): Language {
        return $this->handleCommand(new LanguageCreateCommand($code, $flag, $name));
    }

    protected function createMetadata(string $name, array $label, array $description, array $placeholder, string $control): Metadata {
        return $this->handleCommand(new MetadataCreateCommand($name, $label, $description, $placeholder, $control));
    }

    protected function createResourceKind(array $label, array $metadataList): ResourceKind {
        return $this->handleCommand(new ResourceKindCreateCommand($label, $metadataList));
    }

    /** Creates arrays for use in createResourceKind()'s $metadataList */
    protected function resourceKindMetadata(Metadata $baseMetadata, array $label, array $description = [], array $placeholder = []): array {
        return [
            'base_id' => $baseMetadata->getId(),
            'label' => $label,
            'description' => $description,
            'placeholder' => $placeholder
        ];
    }

    protected function createResource(ResourceKind $resourceKind, array $contents): ResourceEntity {
        return $this->handleCommand(new ResourceCreateCommand($resourceKind, $contents));
    }
}
