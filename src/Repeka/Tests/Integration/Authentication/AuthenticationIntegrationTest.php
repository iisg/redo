<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class AuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    public function testAuthSuccess() {
        $client = $this->authenticate(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD);
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
        /** @var UserEntity $user */
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals(AdminAccountFixture::USERNAME, $user->getUsername());
    }

    public function testAuthSuccessWithUppercaseUsername() {
        $client = $this->authenticate(strtoupper(AdminAccountFixture::USERNAME), AdminAccountFixture::PASSWORD);
        /** @var UserEntity $user */
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals(AdminAccountFixture::USERNAME, $user->getUsername());
    }

    public function testAuditAuthSuccess() {
        $this->testAuthSuccess();
        /** @var AuditEntryRepository $auditRepository */
        $auditRepository = $this->container->get(AuditEntryRepository::class);
        $entries = $auditRepository->findAll();
        $entry = end($entries);
        $this->assertEquals(AdminAccountFixture::USERNAME, $entry->getUser()->getUsername());
        $this->assertEquals('user_authenticate', $entry->getCommandName());
        $this->assertTrue($entry->isSuccessful());
    }

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
        $this->assertContains('nieudane', $client->getResponse()->getContent());
    }

    public function testAuditAuthFailure() {
        $this->testAuthFailure();
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
        $buttonCrawlerNode = $crawler->selectButton('Zaloguj');
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
