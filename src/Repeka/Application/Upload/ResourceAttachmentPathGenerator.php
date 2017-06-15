<?php
namespace Repeka\Application\Upload;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceAttachmentPathGenerator {
    private $uploadsRootPath;
    private $tempFolder;

    public function __construct(string $uploadsRootPath, string $tempFolder) {
        $this->uploadsRootPath = $uploadsRootPath;
        $this->tempFolder = $tempFolder;
    }

    /**
     * For a $resource with ID 123 returns the path i1/i2/i3/r123
     */
    public function getDestinationPath(ResourceEntity $resource): string {
        Assertion::integer($resource->getId());
        $trail = $this->wrapEveryCharacter((string)$resource->getId(), 'i', '/');
        return $trail . 'r' . $resource->getId();
    }

    /**
     * wrapEveryCharacter('123', 'i', '/') -> 'i1/i2/i3'
     */
    private function wrapEveryCharacter(string $input, string $prefix, string $suffix): string {
        return preg_replace('/(.)/', $prefix . '$1' . $suffix, $input);
    }

    public function getUploadsRootPath(): string {
        return $this->uploadsRootPath;
    }

    public function getTemporaryFolderName(): string {
        return $this->tempFolder;
    }

    public function getTemporaryPath(): string {
        return $this->getUploadsRootPath() . '/' . $this->getTemporaryFolderName();
    }
}
