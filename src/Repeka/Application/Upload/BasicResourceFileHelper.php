<?php
namespace Repeka\Application\Upload;

use Assert\Assertion;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

class BasicResourceFileHelper implements ResourceFileHelper {
    /** @var ResourceFilePathGenerator */
    private $pathGenerator;
    /** @var FilesystemDriver */
    private $filesystemDriver;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        ResourceFilePathGenerator $pathGenerator,
        FilesystemDriver $filesystemDriver,
        MetadataRepository $metadataRepository
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->filesystemDriver = $filesystemDriver;
        $this->metadataRepository = $metadataRepository;
    }

    public function moveFilesToDestinationPaths(ResourceEntity $resource): int {
        Assertion::integer($resource->getId());
        $fileMetadataIds = $this->getPossibleFileMetadataIds($resource);
        $resource->getContents()->forEachValue(function ($filePath, int $metadataId) use ($resource, $fileMetadataIds) {
            if (in_array($metadataId, $fileMetadataIds)) {
                $this->ensureMovingWillNotOverwriteFile($filePath, $resource);
            }
        });
        $movedFilesCount = 0;
        $contents = $resource->getContents()->mapAllValues(
            function ($value, int $metadataId) use ($resource, $fileMetadataIds, &$movedFilesCount) {
                if (in_array($metadataId, $fileMetadataIds)) {
                    $path = $this->moveFileToDestinationPath($value, $resource);
                    if ($path) {
                        $movedFilesCount++;
                        return $path;
                    }
                }
                return $value;
            }
        );
        $resource->updateContents($contents);
        return $movedFilesCount;
    }

    public function getFilesThatWouldBeOverwrittenInDestinationPaths(ResourceEntity $resource): array {
        $fileMetadataIds = $this->getPossibleFileMetadataIds($resource);
        return $resource->getContents()->reduceAllValues(
            function ($value, int $metadataId, array $existingFiles) use ($resource, $fileMetadataIds) {
                if (in_array($metadataId, $fileMetadataIds)) {
                    try {
                        $this->ensureMovingWillNotOverwriteFile($value, $resource);
                    } catch (ResourceFileExistException $e) {
                        $existingFiles[] = $e->getConflictingPath();
                    }
                }
                return $existingFiles;
            },
            []
        );
    }

    private function getPossibleFileMetadataIds(ResourceEntity $resource): array {
        $fileMetadataQuery = MetadataListQuery::builder()
            ->filterByResourceClass($resource->getResourceClass())
            ->filterByControl(MetadataControl::FILE())
            ->build();
        $fileMetadata = $this->metadataRepository->findByQuery($fileMetadataQuery);
        return EntityUtils::mapToIds($fileMetadata);
    }

    /** @param string[] $filePaths */
    private function ensureMovingWillNotOverwriteFile(string $filePath, ResourceEntity $resource): void {
        $relativeTargetPath = $this->getRelativePath($resource, $filePath);
        $absoluteTargetPath = $this->getAbsolutePath($resource, $filePath);
        if ($filePath != $relativeTargetPath && $this->filesystemDriver->exists($absoluteTargetPath)) {
            throw new ResourceFileExistException($resource, $relativeTargetPath);
        }
    }

    /**
     * @param string $filePath
     * @return string relativeTargetPath of the file or false if the file has not been moved
     */
    private function moveFileToDestinationPath(string $filePath, ResourceEntity $resource) {
        $relativeTargetPath = $this->getRelativePath($resource, $filePath);
        if ($filePath != $relativeTargetPath) {
            $absoluteTargetPath = $this->getAbsolutePath($resource, $filePath);
            $absoluteTargetFolder = pathinfo($absoluteTargetPath)['dirname'];
            if (!$this->filesystemDriver->exists($absoluteTargetFolder)) {
                $this->filesystemDriver->mkdirRecursive($absoluteTargetFolder, 0750);
            }
            $this->filesystemDriver->move($this->toAbsolutePath($filePath), $absoluteTargetPath);
            return $relativeTargetPath;
        } else {
            return false;
        }
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
