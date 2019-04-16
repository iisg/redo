<?php
namespace Repeka\Plugins\Redo\Tests\Integration;

use Repeka\Application\Entity\UserEntity;
use Repeka\Plugins\Redo\Authentication\PKAuthenticationClient;
use Repeka\Tests\Integration\Authentication\AuthenticationIntegrationTest;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class PKAuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    /** @beforeClass */
    public static function registerTestPKSoapServiceAndCopyUserMapping() {
        PKAuthenticationClient::$defaultSoapService = new TestPKSoapService();
        copy(\AppKernel::VAR_PATH . '/config/user_data_mapping.json.sample', \AppKernel::VAR_PATH . '/config/user_data_mapping.json');
    }

    /** @afterClass */
    public static function unregisterTestPKSoapServiceAndDeleteUserMapping() {
        PKAuthenticationClient::$defaultSoapService = null;
        unlink(\AppKernel::VAR_PATH . '/config/user_data_mapping.json');
    }

    public function testAuthSuccessWithHashedPassword() {
        $client = AuthenticationIntegrationTest::authenticate('b/123456', 'piotr');
        $this->assertNotContains('error-message', $client->getResponse()->getContent());
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/123456', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię');
        $this->assertEquals(['Piotr'], $user->getUserData()->getValues($nameMetadata));
    }

    public function testUnsupportedUsernameAuthFailure() {
        $client = AuthenticationIntegrationTest::authenticate('a/123456', 'piotr');
        $this->assertContains('error-message', $client->getResponse()->getContent());
        $this->assertNull(AuthenticationIntegrationTest::getAuthenticatedUser($client));
    }

    public function testAuthSuccessWithPlainPassword() {
        $client = AuthenticationIntegrationTest::authenticate('b/012345', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('b/012345', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię');
        $this->assertEquals(['Halina'], $user->getUserData()->getValues($nameMetadata));
        $emailMetadata = $this->findMetadataByName('Email');
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
