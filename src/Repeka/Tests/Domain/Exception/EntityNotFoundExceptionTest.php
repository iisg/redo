<?php
namespace Repeka\Tests\Domain\Exception;

use Repeka\Domain\Exception\EntityNotFoundException;

class EntityNotFoundExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testGettingEntityNameFromString() {
        $exception = new EntityNotFoundException('Dummy', '');
        $this->assertEquals('entityNotFound', $exception->getErrorMessageId());
    }

    public function testGettingEntityNameFromRepository() {
        $exception = new EntityNotFoundException(new DummyRepository(), '');
        $this->assertEquals('Dummy', $exception->getParams()['entityName']);
    }

    public function testGettingEntityNameFromDoctrineRepository() {
        $exception = new EntityNotFoundException(new DummyDoctrineRepository(), '');
        $this->assertEquals('Dummy', $exception->getParams()['entityName']);
    }

    public function testStrippingSuffixes() {
        $exception = new EntityNotFoundException('DummyRepository', '');
        $this->assertEquals('Dummy', $exception->getParams()['entityName']);
    }

    public function testFormattingStringQuery() {
        $exception = new EntityNotFoundException('', 'test');
        $this->assertEquals('"test"', $exception->getParams()['query']);
    }

    public function testFormattingIntQuery() {
        $exception = new EntityNotFoundException('', 123);
        $this->assertEquals('#123', $exception->getParams()['query']);
    }
}

// @codingStandardsIgnoreStart
class DummyRepository {
}

class DummyDoctrineRepository {
}
