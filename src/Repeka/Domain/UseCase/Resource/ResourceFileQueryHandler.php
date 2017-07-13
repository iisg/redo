<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Upload\ResourceAttachmentHelper;

class ResourceFileQueryHandler {
    /** @var ResourceAttachmentHelper */
    private $resourceAttachmentHelper;

    public function __construct(ResourceAttachmentHelper $resourceAttachmentHelper) {
        $this->resourceAttachmentHelper = $resourceAttachmentHelper;
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
                    return $this->resourceAttachmentHelper->toAbsolutePath($filePath);
                }
            }
        }
        throw new NotFoundException("File $filename does not exist in the resource #{$resource->getId()}.");
    }
}
