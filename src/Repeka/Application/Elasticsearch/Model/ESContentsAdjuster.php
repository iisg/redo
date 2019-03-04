<?php
namespace Repeka\Application\Elasticsearch\Model;

use Psr\Container\ContainerInterface;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\ResourceFileStorage;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ESContentsAdjuster {
    use ContainerAwareTrait;

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        MetadataRepository $metadataRepository,
        ContainerInterface $container
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->container = $container;
    }

    public function adjustContents(ResourceEntity $resource, $contents): array {
        $resourceFileStorage = $this->container->get(ResourceFileStorage::class);
        $adjustedContents = [];
        foreach ($contents as $key => $values) {
            $adjustedMetadata = [];
            try {
                $metadata = $this->metadataRepository->findOne($key);
            } catch (EntityNotFoundException $e) {
                continue;
            }
            $control = $metadata->getControl();
            foreach ($values as $value) {
                $singleMetadata = [];
                if (!in_array($control, FtsConstants::UNACCEPTABLE_TYPES)) {
                    $singleMetadata['value_' . $control] = $this->adjustMetadataValuesToMappings(
                        $control,
                        $value['value'] ?? '',
                        $resource,
                        $resourceFileStorage
                    );
                }
                if (isset($value['submetadata'])) {
                    $singleMetadata['submetadata'] = $this->adjustContents($resource, $value['submetadata']);
                }
                if (!empty($singleMetadata)) {
                    $adjustedMetadata[] = $singleMetadata;
                }
            }
            if (!empty($adjustedMetadata)) {
                $adjustedContents[$key] = $adjustedMetadata;
            }
        }
        return $adjustedContents;
    }

    private function adjustMetadataValuesToMappings(
        string $control,
        $value,
        ResourceEntity $resource,
        ResourceFileStorage $storage
    ) {
        if ($control == MetadataControl::FILE) {
            return $this->adjustSingleFile($resource, $value, $storage);
        }
        if ($control == MetadataControl::DIRECTORY) {
            return $this->adjustDirectory($resource, $value, $storage);
        }
        return $value;
    }

    private function adjustDirectory(ResourceEntity $resource, string $path, ResourceFileStorage $storage): array {
        $files = $storage->getDirectoryContents($resource, $path);
        $adjustedFiles = [];
        foreach ($files as $file) {
            if ($this->formatSupportedByEs($file)) {
                $adjustedFiles[] = $this->adjustSingleFile($resource, $path . '/' . basename($file), $storage);
            }
        }
        return $adjustedFiles;
    }

    private function adjustSingleFile(ResourceEntity $resource, string $path, ResourceFileStorage $storage): string {
        if ($this->formatSupportedByEs($path)) {
            try {
                $fileContents = $storage->getFileContents($resource, $path);
                if (mb_detect_encoding($fileContents, FtsConstants::SUPPORTED_ENCODING_TYPES, true)) {
                    return $fileContents;
                }
            } catch (NotFoundException $e) {
            }
        }
        return '';
    }

    private function formatSupportedByEs($filename) {
        return in_array(pathinfo($filename, PATHINFO_EXTENSION), FtsConstants::SUPPORTED_FILE_EXTENSIONS);
    }
}
