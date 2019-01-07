<?php
namespace Repeka\Application\Service;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Symfony\Component\HttpFoundation\Response;

class FileSystemResourceFileStorage implements ResourceFileStorage {
    /** @var array */
    private $uploadDirs;
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;
    /** @var FileSystemDriver */
    private $fileSystemDriver;

    public function __construct(
        array $uploadDirs,
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        FileSystemDriver $fileSystemDriver
    ) {
        $this->uploadDirs = $uploadDirs;
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->fileSystemDriver = $fileSystemDriver;
    }

    public function getFileSystemPath(ResourceEntity $resource, string $path): string {
        $uploadDirs = $this->uploadDirsForResource($resource);
        preg_match('#/?(.+?)/(.+)#', $path, $matches);
        if ($matches) {
            list(, $uploadDirId, $filepath) = $matches;
            foreach ($uploadDirs as $uploadDir) {
                if ($uploadDir['id'] == $uploadDirId) {
                    return $uploadDir['path'] . '/' . $filepath;
                }
            }
        }
        throw new DomainException('invalidFileSpec', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function uploadDirsForResource(ResourceEntity $resource): array {
        $uploadDirs = array_map(
            function (array $uploadDir) use ($resource) {
                $uploadDir['path'] = $this->displayStrategyEvaluator->render($resource, $uploadDir['path']);
                return $uploadDir;
            },
            $this->uploadDirs
        );
        foreach ($uploadDirs as &$uploadDir) {
            if (!file_exists($uploadDir['path'])) {
                try {
                    $this->fileSystemDriver->mkdirRecursive($uploadDir['path']);
                } catch (\Exception $e) {
                }
            }
            $uploadDir['path'] = realpath($uploadDir['path']);
        }
        return array_values(
            array_filter(
                $uploadDirs,
                function (array $config) {
                    return $config['path'];
                }
            )
        );
    }
}
