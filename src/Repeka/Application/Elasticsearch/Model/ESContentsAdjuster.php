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
                    $singleMetadata['value_' . $control] = $control != MetadataControl::FILE
                        ? $value['value'] ?? ''
                        : $this->readFileContents($resource, $value['value'], $resourceFileStorage);
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

    private function readFileContents(ResourceEntity $resource, string $path, ResourceFileStorage $storage): array {
        $adjustedFile = ['name' => $this->getFilename($path)];
        if ($this->hasSupportedExtension($path)) {
            try {
                $fileContents = $storage->getFileContents($resource, $path);
                if (mb_detect_encoding($fileContents, FtsConstants::SUPPORTED_ENCODING_TYPES, true)) {
                    $adjustedFile['content'] = $fileContents;
                }
            } catch (NotFoundException $e) {
            }
        }
        return $adjustedFile;
    }

    private function getFilename($path) {
        return preg_replace('%.*/%', '', $path);
    }

    private function hasSupportedExtension($path): bool {
        if (!preg_match('%.+\..+%', $path)) {
            return false;
        }
        $extension = preg_replace('%.*\.%', '', $path);
        return in_array($extension, FtsConstants::SUPPORTED_FILE_EXTENSIONS);
    }
}
