<?php
namespace Repeka\Tests\Application\Authentication;

use Repeka\Application\Authentication\PKAuthenticationException;

class PKAuthenticationExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testRemovingPasswordsFromArrays() {
        $e = new PKAuthenticationException(
            '',
            [
                'abc' => 'qwe',
                'password' => 'SmV0IEZ1ZWwgQ2FuJ3QgTWVsdCBTdGVlbCBCZWFtcw==',
                'plainPassword' => 'p4ssw0rd',
            ]
        );
        $result = $e->getData();
        $this->assertCount(3, $result);
        $this->assertEquals('qwe', $result['abc']);
        $this->assertContains('redacted', $result['password']);
        $this->assertContains('redacted', $result['plainPassword']);
    }

    public function testRemovingPasswordsFromDeepArrays() {
        $e = new PKAuthenticationException(
            '',
            [
                'abc' => 'qwe',
                'credentials' => [
                    'username' => 'bolek',
                    'plainPassword' => 'bolek4ever',
                ],
            ]
        );
        $result = $e->getData();
        $this->assertCount(2, $result);
        $this->assertEquals('qwe', $result['abc']);
        $subResult = $result['credentials'];
        $this->assertCount(2, $subResult);
        $this->assertEquals('bolek', $subResult['username']);
        $this->assertContains('redacted', $subResult['plainPassword']);
    }

    public function testRemovingPasswordsFromObjects() {
        $e = new PKAuthenticationException(
            '',
            (object)[
                'abc' => 'qwe',
                'password' => 'SmV0IEZ1ZWwgQ2FuJ3QgTWVsdCBTdGVlbCBCZWFtcw==',
                'plainPassword' => 'p4ssw0rd',
            ]
        );
        $result = $e->getData();
        $this->assertEquals('qwe', $result->abc);
        $this->assertContains('redacted', $result->password);
        $this->assertContains('redacted', $result->plainPassword);
    }

    public function testRemovingPasswordsFromDeepObjects() {
        $e = new PKAuthenticationException(
            '',
            (object)[
                'abc' => 'qwe',
                'credentials' => (object)[
                    'username' => 'bolek',
                    'plainPassword' => 'bolek4ever',
                ],
            ]
        );
        $result = $e->getData();
        $this->assertEquals('qwe', $result->abc);
        $subResult = $result->credentials;
        $this->assertEquals('bolek', $subResult->username);
        $this->assertContains('redacted', $subResult->plainPassword);
    }

    public function testReturningNonTraversableObjectsUntouched() {
        $value = 'test';
        $e = new PKAuthenticationException('', $value);
        $this->assertSame($value, $e->getData());
    }
}
