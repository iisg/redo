<?php
namespace Repeka\Tests\Domain\Exception;

use Repeka\Domain\Exception\EntityNotFoundException;

class EntityNotFoundExceptionTest extends \PHPUnit_Framework_TestCase {
    public function testGettingEntityNameFromString() {
        $exception = new EntityNotFoundException('Dummy', '');
        $this->assertContains('Not found: Dummy ', $exception->getMessage());
    }

    public function testGettingEntityNameFromRepository() {
        $exception = new EntityNotFoundException(new DummyRepository(), '');
        $this->assertContains('Not found: Dummy ', $exception->getMessage());
    }

    public function testGettingEntityNameFromDoctrineRepository() {
        $exception = new EntityNotFoundException(new DummyDoctrineRepository(), '');
        $this->assertContains('Not found: Dummy ', $exception->getMessage());
    }

    public function testFormattingStringQuery() {
        $exception = new EntityNotFoundException('', 'test');
        $this->assertContains('"test"', $exception->getMessage());
    }

    public function testFormattingIntQuery() {
        $exception = new EntityNotFoundException('', 123);
        $this->assertContains('#123', $exception->getMessage());
    }
}

// @codingStandardsIgnoreStart
class DummyRepository {
}

class DummyDoctrineRepository {
}
