<?php
namespace Repeka\Tests\Application\EventListener;

use Psr\Log\LoggerInterface;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\EventListener\GlobalExceptionListener;
use Repeka\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class GlobalExceptionListenerTest extends \PHPUnit_Framework_TestCase {
    /** @var  GlobalExceptionListener */
    private $listener;

    /** @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $tokenStorage;
    /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $session;
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    public function setUp() {
        $this->tokenStorage = $this->createMock(TokenStorage::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testHandleException() {
        $this->listener = new GlobalExceptionListener(true, $this->tokenStorage, $this->session, $this->logger);
        $mockedEvent = $this->createMock(GetResponseForExceptionEvent::class);
        $mockedEvent->expects($this->once())->method('getException')->willReturn(new DomainException('a'));
        $mockedEvent->expects($this->once())->method('setResponse');
        $mockedEvent->method('getRequest')->willReturn($this->createMock(Request::class));
        $this->logger->expects($this->once())->method('error');
        $this->listener->onException($mockedEvent);
    }

    public function testDomainExceptionResponse() {
        $this->listener = new GlobalExceptionListener(false, $this->tokenStorage, $this->session, $this->logger);
        $response = $this->listener->createErrorResponse(new DomainException('Error'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Error',], 400);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testAuthenticationMissingExceptionResponse() {
        $mockedUser = $this->createMock(TokenInterface::class);
        $mockedUser->method('getUser')->willReturn(null);
        $mockedToken = $this->createMock(TokenStorage::class);
        $mockedToken->method('getToken')->willReturn($mockedUser);
        $this->listener = new GlobalExceptionListener(false, $mockedToken, $this->session, $this->logger);
        $response = $this->listener->createErrorResponse(new AuthenticationException(), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Unauthorized'], 401);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testAccessDeniedExceptionResponse() {
        $mockedUser = $this->createMock(TokenInterface::class);
        $mockedUser->method('getUser')->willReturn($this->createMock(UserEntity::class));
        $mockedToken = $this->createMock(TokenStorage::class);
        $mockedToken->method('getToken')->willReturn($mockedUser);
        $this->listener = new GlobalExceptionListener(false, $mockedToken, $this->session, $this->logger);
        $response = $this->listener->createErrorResponse(new AccessDeniedException('Forbidden'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Forbidden'], 403);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testOtherExceptionResponses() {
        $this->listener = new GlobalExceptionListener(false, $this->tokenStorage, $this->session, $this->logger);
        $response = $this->listener->createErrorResponse(new \Exception('ExceptionError'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'Internal server error.'], 500);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDisplayingOtherExceptionMessageIfDebug() {
        $this->listener = new GlobalExceptionListener(true, $this->tokenStorage, $this->session, $this->logger);
        $response = $this->listener->createErrorResponse(new \Exception('ExceptionError'), new Request());
        $expectedResponse = new JsonResponse(['message' => 'ExceptionError'], 500);
        $this->assertEquals($expectedResponse, $response);
    }
}
