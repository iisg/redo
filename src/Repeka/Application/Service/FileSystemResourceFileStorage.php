<?php
namespace Repeka\Application\Service;

use Exception;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\Utils\StringUtils;
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

    public function getFileSystemPath(ResourceEntity $resource, string $resourcePath): string {
        $uploadDirs = $this->uploadDirsForResource($resource);
        preg_match('#/?(.+?)/(.+)#', $resourcePath, $matches);
        if ($matches) {
            list(, $uploadDirId, $filepath) = $matches;
            foreach ($uploadDirs as $uploadDir) {
                if ($uploadDir['id'] == $uploadDirId) {
                    return StringUtils::joinPaths($uploadDir['path'], $filepath);
                }
            }
        }
        throw new DomainException('invalidFileSpec', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function getResourcePath(ResourceEntity $resource, string $fileSystemPath): string {
        $uploadDirs = $this->uploadDirsForResource($resource);
        foreach ($uploadDirs as $uploadDir) {
            if (strpos($fileSystemPath, $uploadDir['path']) === 0) {
                return StringUtils::joinPaths($uploadDir['id'], substr($fileSystemPath, strlen($uploadDir['path'])));
            }
        }
        throw new DomainException('invalidFileSpec', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function uploadDirsForResource(ResourceEntity $resource): array {
        $uploadDirs = [];
        foreach ($this->uploadDirs as $uploadDir) {
            $id = $uploadDir['id'];
            if (!isset($uploadDirs[$id])) {
                $uploadDirs[$id] = [];
            }
            $uploadDirs[$id] = array_replace($uploadDirs[$id], $uploadDir);
            $uploadDirs[$id]['path'] = $this->displayStrategyEvaluator->render($resource, $uploadDirs[$id]['path']);
        }
        foreach ($uploadDirs as &$uploadDir) {
            if ($uploadDir['condition']) {
                $conditionMet = trim($this->displayStrategyEvaluator->render($resource, $uploadDir['condition']));
                if (!$conditionMet) {
                    $uploadDir['path'] = null;
                }
            }
            if ($uploadDir['path']) {
                if (!file_exists($uploadDir['path'])) {
                    try {
                        $this->fileSystemDriver->mkdirRecursive($uploadDir['path']);
                    } catch (Exception $e) {
                    }
                }
                $uploadDir['path'] = StringUtils::unixSlashes(realpath($uploadDir['path']));
            }
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

    public function getFileContents(ResourceEntity $resource, string $path): string {
        $fullPath = $this->getFileSystemPath($resource, $path);
        return is_file($fullPath)
            ? file_get_contents($fullPath)
            : '';
    }

    public function getDirectoryContents(ResourceEntity $resource, string $path): array {
        $fullPath = $this->getFileSystemPath($resource, $path);
        return array_map(
            function ($filename) use ($path) {
                return StringUtils::joinPaths($path, $filename);
            },
            $this->fileSystemDriver->listDirectory($fullPath)
        );
    }
}
