<?php
namespace Repeka\Application\Twig;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Utils\ImmutableIteratorAggregate;
use Repeka\Domain\Utils\PrintableArray;

class TwigFileMetadataExtension extends \Twig_Extension {
    use CommandBusAware;

    /** @var ResourceFileStorage */
    private $resourceFileStorage;
    /** @var FileSystemDriver */
    private $fileSystemDriver;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceFileStorage $resourceFileStorage,
        FileSystemDriver $fileSystemDriver
    ) {
        $this->resourceFileStorage = $resourceFileStorage;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->metadataRepository = $metadataRepository;
    }

    public function getFunctions() {
        return [];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('metadataFiles', [$this, 'metadataFiles']),
            new \Twig_Filter('metadataImageFiles', [$this, 'metadataImageFiles']),
            new \Twig_Filter('metadataFileSize', [$this, 'metadataFileSize'], ['needs_context' => true]),
            new \Twig_Filter('formatBytes', [$this, 'formatBytes']),
        ];
    }

    /** @throws \Twig_Error */
    public function metadataFiles(ResourceEntity $resource, $metadata, array $allowedExtensions = []) {
        if (!$metadata instanceof Metadata) {
            $metadata = $this->metadataRepository->findByNameOrId($metadata);
        }
        if (!in_array($metadata->getControl(), [MetadataControl::FILE, MetadataControl::DIRECTORY])) {
            $name = $metadata->getName();
            throw new \Twig_Error("Metadata $name does not specify files.");
        }
        $filenames = $resource->getContents()->getValuesWithoutSubmetadata($metadata);
        if ($metadata->getControl() == MetadataControl::DIRECTORY) {
            $filenames = $this->mapDirectoriesToTheirFiles($resource, $filenames);
        }
        if (!empty($allowedExtensions)) {
            $filenames = array_values(
                array_filter(
                    $filenames,
                    function ($filename) use ($allowedExtensions) {
                        return in_array(pathinfo($filename, PATHINFO_EXTENSION), $allowedExtensions);
                    }
                )
            );
        }
        return $filenames;
    }

    private function mapDirectoriesToTheirFiles(ResourceEntity $resource, array $directoryNames) {
        return ArrayUtils::flatten(
            array_map(
                function ($directoryName) use ($resource) {
                    return $this->resourceFileStorage->getDirectoryContents($resource, $directoryName);
                },
                $directoryNames
            )
        );
    }

    /** @throws \Twig_Error */
    public function metadataImageFiles(ResourceEntity $resource, string $metadata) {
        $filenames = $this->metadataFiles($resource, $metadata, ['jpg', 'png', 'jpeg']);
        $arr = array_map(
            function ($filename) use ($resource) {
                $systemPath = $this->resourceFileStorage->getFileSystemPath($resource, $filename);
                $dimensions = $this->fileSystemDriver->getImageDimensions($systemPath);
                return [
                    'path' => $filename,
                    'w' => $dimensions['width'],
                    'h' => $dimensions['height'],
                ];
            },
            $filenames
        );
        return $arr;
    }

    public function metadataFileSize(array $context, $filename, ResourceEntity $resource = null) {
        if ($filename instanceof ImmutableIteratorAggregate) {
            $filename = $filename->toArray();
        }
        if (is_array($filename)) {
            return new PrintableArray(
                array_map(
                    function ($filename) use ($context, $resource) {
                        return $this->metadataFileSize($context, $filename, $resource);
                    },
                    $filename
                )
            );
        } else {
            $resource = $this->getResourceFromContext($context, $resource);
            $systemPath = $this->resourceFileStorage->getFileSystemPath($resource, $filename);
            return $this->fileSystemDriver->getFileSize($systemPath);
        }
    }

    /** @see https://stackoverflow.com/a/37523842/878514 */
    public function formatBytes($bytes, int $precision = 1): string {
        if ($bytes instanceof ImmutableIteratorAggregate) {
            $bytes = $bytes->toArray();
        }
        if (is_array($bytes)) {
            return new PrintableArray(
                array_map(
                    function ($bytes) use ($precision) {
                        return $this->formatBytes($bytes, $precision);
                    },
                    $bytes
                )
            );
        } else {
            $units = ['B', 'kB', 'MB', 'GB', 'TB'];
            $bytes = max((string)$bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }

    private function getResourceFromContext(array $context, ResourceEntity $useThisIfGiven = null): ResourceEntity {
        if ($useThisIfGiven) {
            $resource = $useThisIfGiven;
        } elseif (isset($context['resource'])) {
            $resource = $context['resource'];
        } else {
            $resource = $context['r'] ?? null;
        }
        Assertion::isInstanceOf($resource, ResourceEntity::class, 'Could not find resource in display strategy context.');
        return $resource;
    }
}
