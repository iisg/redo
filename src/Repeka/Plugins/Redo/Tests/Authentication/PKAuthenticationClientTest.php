<?php
namespace Repeka\Plugins\Redo\Tests\Authentication;

use Repeka\Plugins\Redo\Authentication\PKAuthenticationClient;
use Repeka\Plugins\Redo\Authentication\PKSoapService;

class PKAuthenticationClientTest extends \PHPUnit_Framework_TestCase {
    /** @var PKAuthenticationClient */
    private $authClient;

    /** @var PKSoapService|\PHPUnit_Framework_MockObject_MockObject */
    private $soapService;

    /** @before */
    public function createAuthClient() {
        $this->soapService = $this->getMockBuilder(PKSoapService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClientDataById', 'isValidPassword'])
            ->getMock();
        PKAuthenticationClient::$defaultSoapService = $this->soapService;
        $this->authClient = new PKAuthenticationClient(null, []);
    }

    /** @afterClass */
    public static function unregisterTestPKSoapService() {
        PKAuthenticationClient::$defaultSoapService = null;
    }

    public function testFetchesUserData() {
        $this->soapService->expects($this->once())->method('getClientDataById')->willReturn(['plainPassword' => '']);
        $this->authClient->authenticate('b/123456', 'whatever');
    }

    public function testFetchesUserDataOfUsernameOfLength10() {
        $this->soapService->expects($this->once())->method('getClientDataById')->willReturn(['plainPassword' => '']);
        $this->authClient->authenticate('1234567890', 'whatever');
    }

    public function testRejectsUnsupportedUsernames() {
        $this->soapService->expects($this->never())->method('getClientDataById');
        foreach (['b/123', '123', '123456789', 'b/1234567890'] as $unsupportedUsername) {
            $this->assertFalse($this->authClient->authenticate($unsupportedUsername, 'whatever'));
        }
    }

    public function testAuthenticatesWithMatchingPlainPassword() {
        $this->soapService->method('getClientDataById')->willReturn(['plainPassword' => 'p4ssw0rd']);
        $this->soapService->expects($this->never())->method('isValidPassword');
        $result = $this->authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertTrue($result);
    }

    public function testRejectsDifferentPlainPassword() {
        $this->soapService->method('getClientDataById')->willReturn(['plainPassword' => 'p4ssw0rd']);
        $result = $this->authClient->authenticate('b/123456', 'BADp4ssw0rd');
        $this->assertFalse($result);
    }

    public function testAuthenticatedWithMatchingCipheredPassword() {
        $this->soapService->method('getClientDataById')->willReturn(['password' => '!qwerty!']);
        $this->soapService->expects($this->once())->method('isValidPassword')->with('p4ssw0rd', '!qwerty!')->willReturn(true);
        $result = $this->authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertTrue($result);
    }

    public function testRejectsDifferentCipheredPassword() {
        $this->soapService->method('getClientDataById')->willReturn(['password' => '!qwerty!']);
        $this->soapService->method('isValidPassword')->willReturn(false);
        $result = $this->authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertFalse($result);
    }

    public function testFailsIfNoKnownAuthenticationMethodsAvailable() {
        $this->expectException(\Exception::class);
        $this->soapService->method('getClientDataById')->willReturn([]);
        $this->soapService->expects($this->never())->method('isValidPassword');
        $this->authClient->authenticate('b/123456', 'whatever');
    }

    public function testThrowsWhenServiceReturnsInvalidData() {
        $this->expectException(\Exception::class);
        $this->soapService->method('getClientDataById')->willReturn('surprise!');
        $this->authClient->authenticate('b/123456', 'whatever');
    }
}
