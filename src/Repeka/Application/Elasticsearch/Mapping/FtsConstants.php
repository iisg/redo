<?php
namespace Repeka\Application\Elasticsearch\Mapping;

use Repeka\Domain\Entity\MetadataControl;

final class FtsConstants {
    const ES_DOCUMENT_TYPE = 'resource';

    const VALUE_TYPE = 'type';

    const ID = 'id';
    const RESOURCE_CLASS = 'resourceClass';
    const KIND_ID = 'kindId';
    const CONTENTS = 'contents';

    const UNACCEPTABLE_TYPES = [
        MetadataControl::FILE,
        MetadataControl::FLEXIBLE_DATE,
    ];

    const NUMERIC_DETECTION_PARAM = 'numeric_detection';

    private function __construct() {
    }
}
