<?php
namespace Repeka\Application\Service;

use PHPUnit_Framework_TestCase;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Tests\Traits\StubsTrait;

class FileSystemResourceFileStorageTest extends PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FileSystemResourceFileStorage */
    private $storage;
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    /** @before */
    public function init() {
        $this->displayStrategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->displayStrategyEvaluator->method('render')->willReturnArgument(1);
        $this->storage = new FileSystemResourceFileStorage(
            [
                ['id' => 'here', 'path' => __DIR__, 'label' => 'This directory', 'condition' => null],
            ],
            $this->displayStrategyEvaluator,
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

    public function testOverridingUploadDirsConfig() {
        $this->storage = new FileSystemResourceFileStorage(
            [
                ['id' => 'here', 'path' => __DIR__, 'label' => 'This directory', 'condition' => null],
                ['id' => 'there', 'path' => __DIR__, 'label' => 'This directory', 'condition' => null],
                ['id' => 'here', 'label' => 'New directory', 'condition' => 'a'],
            ],
            $this->displayStrategyEvaluator,
            $this->createMock(FileSystemDriver::class)
        );
        $uploadDirs = $this->storage->uploadDirsForResource($this->createResourceMock(1));
        $this->assertCount(2, $uploadDirs);
        $this->assertEquals('New directory', $uploadDirs[0]['label']);
        $this->assertEquals('a', $uploadDirs[0]['condition']);
    }
}
