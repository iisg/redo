<?php
namespace Repeka\Tests\Integration;

use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class AuthenticationIntegrationTest extends IntegrationTestCase {
    const LOGIN_PATH = '/login';

    public function testAuthSuccess() {
        $client = $this->authenticate(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD);
        /** @var UserEntity $user */
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals(AdminAccountFixture::USERNAME, $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
    }

    public function testAuthFailure() {
        $client = $this->authenticate(AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD . '1');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNull($user);
        $this->assertContains('nieudane', $client->getResponse()->getContent());
    }

    private function authenticate(string $username, string $password): Client {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $buttonCrawlerNode = $crawler->selectButton('Zaloguj');
        $form = $buttonCrawlerNode->form();
        $data = ['_username' => $username, '_password' => $password];
        $client->submit($form, $data);
        $client->followRedirect();
        return $client;
    }

    private function getAuthenticatedUser(Client $client) {
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
