<?php

namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceFileQueryHandler {
    /** @var ResourceFileHelper */
    private $resourceFileHelper;

    public function __construct(ResourceFileHelper $resourceFileHelper) {
        $this->resourceFileHelper = $resourceFileHelper;
    }

    /**
     * @return ResourceEntity[]
     */
    public function handle(ResourceFileQuery $query): string {
        $resource = $query->getResource();
        $filename = $query->getFilename();
        $fileMetadata = $resource->getKind()->getMetadataByControl('file');
        foreach ($fileMetadata as $metadata) {
            $filePaths = $resource->getValues($metadata);
            foreach ($filePaths as $filePath) {
                if (basename($filePath) == $filename) {
                    return $this->resourceFileHelper->toAbsolutePath($filePath);
                }
            }
        }
        throw new NotFoundException("File $filename does not exist in the resource #{$resource->getId()}.");
    }
}
