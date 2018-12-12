<?php
namespace Repeka\Application\Service;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Symfony\Component\HttpFoundation\Response;

class FileSystemResourceFileStorage implements ResourceFileStorage {
    /** @var array */
    private $uploadDirs;
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(array $uploadDirs, ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->uploadDirs = $uploadDirs;
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
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
                $this->mkdirRecursive($uploadDir['path']);
            }
            $uploadDir['path'] = realpath($uploadDir['path']);
        }
        return $uploadDirs;
    }

    private function mkdirRecursive(string $path) {
        if (!file_exists($path)) {
            $result = mkdir($path, 0750, true);
            Assertion::true($result, 'Could not create upload dir: ' . $path);
        }
    }
}
