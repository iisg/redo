<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Entity\UserEntity;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class PKAuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    public function testBudynekAuthSuccess() {
        $client = AuthenticationIntegrationTest::authenticate('budynek', 'piotr');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('budynek', $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Piotr'], $user->getUserData()->getValues($nameMetadata));
    }

    public function testBudynekAuthFailure() {
        $client = AuthenticationIntegrationTest::authenticate('budynek', 'piotrek');
        $this->assertContains('nieudane', $client->getResponse()->getContent());
        $this->assertNull(AuthenticationIntegrationTest::getAuthenticatedUser($client));
    }

    public function testHalinkaAuthSuccess() {
        $client = AuthenticationIntegrationTest::authenticate('halinka', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('halinka', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Halina'], $user->getUserData()->getValues($nameMetadata));
        $emailMetadata = $this->findMetadataByName('Email', $user->getUserData()->getResourceClass());
        $this->assertEquals(['halinka@repeka.pl'], $user->getUserData()->getValues($emailMetadata));
    }

    public function testAuthSuccessWithUpperBPrefix() {
        $client = AuthenticationIntegrationTest::authenticate('B/012345', 'admin');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
    }

    public function testAuthSuccessWithLowerBPrefix() {
        $client = AuthenticationIntegrationTest::authenticate('b/012345', 'admin');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
    }

    public function testAuthSuccessWithoutBPrefix() {
        $client = AuthenticationIntegrationTest::authenticate('012345', 'admin');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
    }
}
