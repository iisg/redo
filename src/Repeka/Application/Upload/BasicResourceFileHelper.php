<?php
namespace Repeka\Application\Upload;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Upload\ResourceFileHelper;

class BasicResourceFileHelper implements ResourceFileHelper {
    /** @var ResourceFilePathGenerator */
    private $pathGenerator;
    /** @var FilesystemDriver */
    private $filesystemDriver;

    public function __construct(ResourceFilePathGenerator $pathGenerator, FilesystemDriver $filesystemDriver) {
        $this->pathGenerator = $pathGenerator;
        $this->filesystemDriver = $filesystemDriver;
    }

    public function moveFilesToDestinationPaths(ResourceEntity $resource): int {
        Assertion::integer($resource->getId());
        $contents = $resource->getContents();
        $fileMetadataIds = $this->getExistingFileMetadataIds($resource);
        foreach ($fileMetadataIds as $metadataId) {
            $this->ensureMovingWillNotOverwriteFiles($contents[$metadataId], $resource);
        }
        $movedFilesCount = 0;
        foreach ($fileMetadataIds as $metadataId) {
            $updatedPaths = $this->moveFilesInListToDestinationPaths($contents[$metadataId], $resource);
            $movedFilesCount += count(array_diff($contents[$metadataId], $updatedPaths));
            $contents[$metadataId] = $updatedPaths;
        }
        $resource->updateContents($contents);
        return $movedFilesCount;
    }

    private function getExistingFileMetadataIds(ResourceEntity $resource): array {
        $fileMetadataList = array_values(array_filter(
            $resource->getKind()->getMetadataList(),
            function (Metadata $metadata) {
                return $metadata->getControl() == 'file';
            }
        ));
        $fileMetadataBaseIds = array_map(function (Metadata $metadata) {
            return $metadata->getBaseId();
        }, $fileMetadataList);
        $contentsIds = array_keys($resource->getContents());
        return array_intersect($fileMetadataBaseIds, $contentsIds);
    }

    public function getFilesThatWouldBeOverwrittenInDestinationPaths(ResourceEntity $resource): array {
        $contents = $resource->getContents();
        $fileMetadataIds = $this->getExistingFileMetadataIds($resource);
        $existingFiles = [];
        foreach ($fileMetadataIds as $metadataId) {
            try {
                $this->ensureMovingWillNotOverwriteFiles($contents[$metadataId], $resource);
            } catch (ResourceFilesExistException $e) {
                $existingFiles = $existingFiles + $e->getExistingFiles();
            }
        }
        return $existingFiles;
    }

    /** @param string[] $filePaths */
    private function ensureMovingWillNotOverwriteFiles(array $filePaths, ResourceEntity $resource): void {
        $existingFiles = [];
        foreach ($filePaths as $path) {
            $relativeTargetPath = $this->getRelativePath($resource, $path);
            $absoluteTargetPath = $this->getAbsolutePath($resource, $path);
            if ($path != $relativeTargetPath && $this->filesystemDriver->exists($absoluteTargetPath)) {
                $existingFiles[$path] = $relativeTargetPath;
            }
        }
        if (count($existingFiles) > 0) {
            throw new ResourceFilesExistException($resource, $existingFiles);
        }
    }

    /**
     * @param string[] $filePaths
     * @return string[]
     */
    private function moveFilesInListToDestinationPaths(array $filePaths, ResourceEntity $resource): array {
        $movedFilePaths = [];
        foreach ($filePaths as $path) {
            $relativeTargetPath = $this->getRelativePath($resource, $path);
            if ($path != $relativeTargetPath) {
                $absoluteTargetPath = $this->getAbsolutePath($resource, $path);
                $absoluteTargetFolder = pathinfo($absoluteTargetPath)['dirname'];
                if (!$this->filesystemDriver->exists($absoluteTargetFolder)) {
                    $this->filesystemDriver->mkdirRecursive($absoluteTargetFolder, 0750);
                }
                $this->filesystemDriver->move($this->toAbsolutePath($path), $absoluteTargetPath);
            }
            $movedFilePaths[] = $relativeTargetPath;
        }
        return $movedFilePaths;
    }

    public function toAbsolutePath(string $path): string {
        return $this->pathGenerator->getUploadsRootPath() . '/' . $path;
    }

    private function getRelativePath(ResourceEntity $resource, string $filePath): string {
        $targetFolder = $this->pathGenerator->getDestinationPath($resource);
        $fileName = basename($filePath);
        $targetPath = $targetFolder . '/' . $fileName;
        return $targetPath;
    }

    private function getAbsolutePath(ResourceEntity $resource, string $filePath): string {
        return $this->toAbsolutePath($this->getRelativePath($resource, $filePath));
    }
}