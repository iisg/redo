<?php
namespace Repeka\Tests\Application\Authentication;

use Repeka\Application\Authentication\PKAuthenticationClient;
use Repeka\Application\Authentication\PKSoapService;

class PKAuthenticationClientTest extends \PHPUnit_Framework_TestCase {
    public function testFetchesUserData() {
        $soapService = $this->createSoapServiceMock();
        $soapService->expects($this->once())->method('getClientDataById')->willReturn(['plainPassword' => '']);
        $authClient = new PKAuthenticationClient($soapService);
        $authClient->authenticate('b/123456', 'whatever');
    }

    public function testFetchesUserDataOfUsernameOfLength10() {
        $soapService = $this->createSoapServiceMock();
        $soapService->expects($this->once())->method('getClientDataById')->willReturn(['plainPassword' => '']);
        $authClient = new PKAuthenticationClient($soapService);
        $authClient->authenticate('1234567890', 'whatever');
    }

    public function testRejectsUnsupportedUsernames() {
        $soapService = $this->createSoapServiceMock();
        $soapService->expects($this->never())->method('getClientDataById');
        $authClient = new PKAuthenticationClient($soapService);
        foreach (['b/123', '123', '123456789', 'b/1234567890'] as $unsupportedUsername) {
            $this->assertFalse($authClient->authenticate($unsupportedUsername, 'whatever'));
        }
    }

    public function testAuthenticatesWithMatchingPlainPassword() {
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn(['plainPassword' => 'p4ssw0rd']);
        $soapService->expects($this->never())->method('isValidPassword');
        $authClient = new PKAuthenticationClient($soapService);
        $result = $authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertTrue($result);
    }

    public function testRejectsDifferentPlainPassword() {
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn(['plainPassword' => 'p4ssw0rd']);
        $authClient = new PKAuthenticationClient($soapService);
        $result = $authClient->authenticate('b/123456', 'BADp4ssw0rd');
        $this->assertFalse($result);
    }

    public function testAuthenticatedWithMatchingCipheredPassword() {
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn(['password' => '!qwerty!']);
        $soapService->expects($this->once())->method('isValidPassword')->with('p4ssw0rd', '!qwerty!')->willReturn(true);
        $authClient = new PKAuthenticationClient($soapService);
        $result = $authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertTrue($result);
    }

    public function testRejectsDifferentCipheredPassword() {
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn(['password' => '!qwerty!']);
        $soapService->method('isValidPassword')->willReturn(false);
        $authClient = new PKAuthenticationClient($soapService);
        $result = $authClient->authenticate('b/123456', 'p4ssw0rd');
        $this->assertFalse($result);
    }

    public function testFailsIfNoKnownAuthenticationMethodsAvailable() {
        $this->expectException(\Exception::class);
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn([]);
        $soapService->expects($this->never())->method('isValidPassword');
        $authClient = new PKAuthenticationClient($soapService);
        $authClient->authenticate('b/123456', 'whatever');
    }

    public function testThrowsWhenServiceReturnsInvalidData() {
        $this->expectException(\Exception::class);
        $soapService = $this->createSoapServiceMock();
        $soapService->method('getClientDataById')->willReturn('surprise!');
        $authClient = new PKAuthenticationClient($soapService);
        $authClient->authenticate('b/123456', 'whatever');
    }

    /** @return PKSoapService|\PHPUnit_Framework_MockObject_MockObject */
    private function createSoapServiceMock(): \PHPUnit_Framework_MockObject_MockObject {
        $soapClient = $this->getMockBuilder(PKSoapService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClientDataById', 'isValidPassword'])
            ->getMock();
        return $soapClient;
    }
}
