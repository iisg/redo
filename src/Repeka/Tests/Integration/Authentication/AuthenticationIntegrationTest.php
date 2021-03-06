<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\Redo\AdminAccountFixture;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class AuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    public function testAuthSuccess() {
        $client = $this->authenticate(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD);
        $this->assertNotContains('failure', $client->getResponse()->getContent());
        /** @var UserEntity $user */
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals(AdminAccountFixture::USERNAME, $user->getUsername());
    }

    /** @depends testAuthSuccess */
    public function testAuditAuthSuccess() {
        /** @var AuditEntryRepository $auditRepository */
        $auditRepository = $this->container->get(AuditEntryRepository::class);
        $entries = $auditRepository->findAll();
        $entry = end($entries);
        $this->assertEquals(AdminAccountFixture::USERNAME, $entry->getUser()->getUsername());
        $this->assertEquals('user_authenticate', $entry->getCommandName());
        $this->assertTrue($entry->isSuccessful());
    }

    /** @small */
    public function testAuthSuccessWithUppercaseUsername() {
        $client = $this->authenticate(strtoupper(AdminAccountFixture::USERNAME), AdminAccountFixture::PASSWORD);
        /** @var UserEntity $user */
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals(AdminAccountFixture::USERNAME, $user->getUsername());
    }

    /** @small */
    public function testUpdateRolesOnAuthSuccess() {
        $admin = $this->getAdminUser();
        $admin->updateRoles([]);
        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();
        $this->testAuthSuccess();
        $admin = $this->getAdminUser();
        $this->assertGreaterThan(2, count($admin->getRoles()));
        $this->assertTrue($admin->hasRole(SystemRole::ADMIN()->roleName()));
    }

    public function testAuthFailure() {
        $client = $this->authenticate(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD . '1');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNull($user);
        $this->assertContains('error-message', $client->getResponse()->getContent());
    }

    /** @depends testAuthFailure */
    public function testAuditAuthFailure() {
        /** @var AuditEntryRepository $auditRepository */
        $auditRepository = $this->container->get(AuditEntryRepository::class);
        $entries = $auditRepository->findAll();
        $entry = end($entries);
        $this->assertNull($entry->getUser());
        $this->assertEquals(AdminAccountFixture::USERNAME, $entry->getData()['username']);
        $this->assertEquals('user_authenticate', $entry->getCommandName());
        $this->assertFalse($entry->isSuccessful());
    }

    public static function authenticate(string $username, string $password): Client {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $buttonCrawlerNode = $crawler->selectButton('login-btn');
        $form = $buttonCrawlerNode->form();
        $data = ['_username' => $username, '_password' => $password];
        $client->submit($form, $data);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        } else {
            throw new \RuntimeException($client->getResponse()->getContent());
        }
        return $client;
    }

    public static function getAuthenticatedUser(Client $client) {
        if (!$client->getContainer()->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }
        if (null === $token = $client->getContainer()->get('security.token_storage')->getToken()) {
            return;
        }
        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }
        return $user;
    }
}
