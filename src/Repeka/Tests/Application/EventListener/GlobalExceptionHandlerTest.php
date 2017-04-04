<?php
namespace Repeka\Tests\Application\EventListener;

use Repeka\Application\EventListener\GlobalExceptionHandler;
use Repeka\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class GlobalExceptionHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var  GlobalExceptionHandler */
    private $handler;

    public function testHandleException() {
        $this->handler = new GlobalExceptionHandler(true);
        $mockedEvent = $this->createMock(GetResponseForExceptionEvent::class);
        $mockedEvent->expects($this->once())->method('getException')->willReturn(new DomainException('a'));
        $mockedEvent->expects($this->once())->method('setResponse');
        $this->handler->onException($mockedEvent);
    }

    public function testHandleDomainException() {
        $this->handler = new GlobalExceptionHandler(false);
        $response = $this->handler->createErrorResponse(new DomainException('Error'));
        $expectedResponse = new JsonResponse([
            'message' => 'Error',
            'data' => []
        ], 400);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testHandleOtherExceptions() {
        $this->handler = new GlobalExceptionHandler(false);
        $response = $this->handler->createErrorResponse(new \Exception('ExceptionError'));
        $expectedResponse = new JsonResponse([
            'message' => 'Internal server error, please try again later'
        ], 500);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDisplayingOtherExceptionMessageIfDebug() {
        $this->handler = new GlobalExceptionHandler(true);
        $response = $this->handler->createErrorResponse(new \Exception('ExceptionError'));
        $expectedResponse = new JsonResponse([
            'message' => 'ExceptionError'
        ], 500);
        $this->assertEquals($expectedResponse, $response);
    }
}
