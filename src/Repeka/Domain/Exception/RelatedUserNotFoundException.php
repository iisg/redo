<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Entity\ResourceEntity;

class RelatedUserNotFoundException extends NotFoundException {
    /** @param ResourceEntity|int $resource */
    public function __construct($resource) {
        $resourceId = is_int($resource) ? $resource : $resource->getId();
        parent::__construct(
            'relatedUserNotFound',
            [
                'resourceId' => $resourceId,
            ]
        );
    }
}
