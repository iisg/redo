<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Entity\UserEntity;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class PKAuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    public function testAuthSuccessWithHashedPassword() {
        $client = AuthenticationIntegrationTest::authenticate('b/123456', 'piotr');
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/123456', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Piotr'], $user->getUserData()->getValues($nameMetadata));
    }

    public function testUnsupportedUsernameAuthFailure() {
        $client = AuthenticationIntegrationTest::authenticate('a/123456', 'piotr');
        $this->assertContains('nieudane', $client->getResponse()->getContent());
        $this->assertNull(AuthenticationIntegrationTest::getAuthenticatedUser($client));
    }

    public function testAuthSuccessWithPlainPassword() {
        $client = AuthenticationIntegrationTest::authenticate('b/012345', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Halina'], $user->getUserData()->getValues($nameMetadata));
        $emailMetadata = $this->findMetadataByName('Email', $user->getUserData()->getResourceClass());
        $this->assertEquals(['halinka@repeka.pl'], $user->getUserData()->getValues($emailMetadata));
    }

    public function testAuthSuccessWithUpperBPrefix() {
        $client = AuthenticationIntegrationTest::authenticate('B/012345', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
    }

    public function testAuthSuccessWithoutBPrefixAndLength6() {
        $client = AuthenticationIntegrationTest::authenticate('012345', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
    }

    public function testAuthSuccessWithUpperSPrefix() {
        $client = AuthenticationIntegrationTest::authenticate('S/123456', 'pass');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('s/123456', $user->getUsername());
    }

    public function testAuthSuccessFor10DigitsUsername() {
        $client = AuthenticationIntegrationTest::authenticate('1234567890', 'pass');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('1234567890', $user->getUsername());
    }
}
