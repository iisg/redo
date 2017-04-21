<?php
namespace Repeka\Tests\Application\EventListener;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\EventListener\GlobalExceptionHandler;
use Repeka\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GlobalExceptionHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var  GlobalExceptionHandler */
    private $handler;

    private $tokenStorage;
    private $session;

    public function setUp() {
        $this->tokenStorage = $this->createMock(TokenStorage::class);
        $this->session = $this->createMock(SessionInterface::class);
    }

    public function testHandleException() {
        $this->handler = new GlobalExceptionHandler(true, $this->tokenStorage, $this->session);
        $mockedEvent = $this->createMock(GetResponseForExceptionEvent::class);
        $mockedEvent->expects($this->once())->method('getException')->willReturn(new DomainException('a'));
        $mockedEvent->expects($this->once())->method('setResponse');
        $mockedEvent->method('getRequest')->willReturn($this->createMock(Request::class));
        $this->handler->onException($mockedEvent);
    }

    public function testHandleDomainException() {
        $this->handler = new GlobalExceptionHandler(false, $this->tokenStorage, $this->session);
        $response = $this->handler->createErrorResponse(new DomainException('Error'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Error',], 400);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testHandleAuthenticationMissingException() {
        $mockedUser = $this->createMock(TokenInterface::class);
        $mockedUser->method('getUser')->willReturn(null);
        $mockedToken = $this->createMock(TokenStorage::class);
        $mockedToken->method('getToken')->willReturn($mockedUser);
        $this->handler = new GlobalExceptionHandler(false, $mockedToken, $this->session);
        $response = $this->handler->createErrorResponse(new AuthenticationException(), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Unauthorized'], 401);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testHandleAccessDeniedException() {
        $mockedUser = $this->createMock(TokenInterface::class);
        $mockedUser->method('getUser')->willReturn($this->createMock(UserEntity::class));
        $mockedToken = $this->createMock(TokenStorage::class);
        $mockedToken->method('getToken')->willReturn($mockedUser);
        $this->handler = new GlobalExceptionHandler(false, $mockedToken, $this->session);
        $response = $this->handler->createErrorResponse(new AccessDeniedException('Forbidden'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Forbidden'], 403);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testHandleOtherExceptions() {
        $this->handler = new GlobalExceptionHandler(false, $this->tokenStorage, $this->session);
        $response = $this->handler->createErrorResponse(new \Exception('ExceptionError'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Internal server error.'], 500);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDisplayingOtherExceptionMessageIfDebug() {
        $this->handler = new GlobalExceptionHandler(true, $this->tokenStorage, $this->session);
        $response = $this->handler->createErrorResponse(new \Exception('ExceptionError'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'ExceptionError'], 500);
        $this->assertEquals($expectedResponse, $response);
    }
}
