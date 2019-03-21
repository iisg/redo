<?php
namespace Repeka\Application\Service;

use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Tests\Traits\StubsTrait;

class FileSystemResourceFileStorageTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FileSystemResourceFileStorage */
    private $storage;

    /** @before */
    public function init() {
        $displayStrategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $displayStrategyEvaluator->method('render')->willReturnArgument(1);
        $this->storage = new FileSystemResourceFileStorage(
            [
                ['id' => 'here', 'path' => __DIR__, 'label' => 'This directory', 'condition' => null],
            ],
            $displayStrategyEvaluator,
            $this->createMock(FileSystemDriver::class)
        );
    }

    public function testResolvingPath() {
        $this->assertEquals(
            StringUtils::joinPaths(__DIR__, __FILE__),
            $this->storage->getFileSystemPath($this->createResourceMock(1), 'here/' . __FILE__)
        );
    }

    public function testResolvingPathForUnknownDir() {
        $this->expectException(DomainException::class);
        $this->storage->getFileSystemPath($this->createResourceMock(1), 'there/' . __FILE__);
    }
}
