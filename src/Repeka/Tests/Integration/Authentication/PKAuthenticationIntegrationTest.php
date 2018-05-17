<?php
namespace Repeka\Tests\Integration\Authentication;

use Repeka\Application\Entity\UserEntity;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class PKAuthenticationIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    public function testBudynekAuthSuccess() {
        $client = AuthenticationIntegrationTest::authenticate('123456', 'piotr');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('123456', $user->getUsername());
        $this->assertNotContains('nieudane', $client->getResponse()->getContent());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Piotr'], $user->getUserData()->getValues($nameMetadata));
    }

    public function testBudynekAuthFailure() {
        $client = AuthenticationIntegrationTest::authenticate('123456', 'piotrek');
        $this->assertContains('nieudane', $client->getResponse()->getContent());
        $this->assertNull(AuthenticationIntegrationTest::getAuthenticatedUser($client));
    }

    public function testHalinkaAuthSuccess() {
        $client = AuthenticationIntegrationTest::authenticate('1234567', 'h4linaRulz');
        /** @var UserEntity $user */
        $user = AuthenticationIntegrationTest::getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('1234567', $user->getUsername());
        $nameMetadata = $this->findMetadataByName('Imię', $user->getUserData()->getResourceClass());
        $this->assertEquals(['Halina'], $user->getUserData()->getValues($nameMetadata));
        $emailMetadata = $this->findMetadataByName('Email', $user->getUserData()->getResourceClass());
        $this->assertEquals(['halinka@repeka.pl'], $user->getUserData()->getValues($emailMetadata));
    }
}
