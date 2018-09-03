<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\EntityUtils;

class ResourceFileQueryHandler {
    /** @var ResourceFileHelper */
    private $resourceFileHelper;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceFileHelper $resourceFileHelper, MetadataRepository $metadataRepository) {
        $this->resourceFileHelper = $resourceFileHelper;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @return ResourceEntity[]
     */
    public function handle(ResourceFileQuery $query): string {
        $resource = $query->getResource();
        $filename = $query->getFilename();
        $fileMetadataQuery = MetadataListQuery::builder()
            ->filterByResourceClass($resource->getResourceClass())
            ->filterByControl(MetadataControl::FILE())
            ->build();
        $fileMetadata = $this->metadataRepository->findByQuery($fileMetadataQuery);
        $fileMetadataIds = EntityUtils::mapToIds($fileMetadata);
        $path = $resource->getContents()->reduceAllValues(
            function (MetadataValue $value, int $metadataId, $path) use ($filename, $fileMetadataIds) {
                if (!$path && in_array($metadataId, $fileMetadataIds) && basename($value->getValue()) == $filename) {
                    return $value->getValue();
                }
                return $path;
            }
        );
        if (!$path) {
            throw new NotFoundException(
                'noSuchResourceFile',
                [
                    'resourceId' => $resource->getId(),
                    'filename' => $filename,
                ]
            );
        }
        return $this->resourceFileHelper->toAbsolutePath($path);
    }
}
