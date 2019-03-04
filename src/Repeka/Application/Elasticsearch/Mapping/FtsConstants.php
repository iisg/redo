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

    const TIMESTAMP_FORMAT = "yyyy-MM-dd'T'HH:mm:ssZZ";
    const FLEXIBLE_DATE_FORMAT = "yyyy-MM-dd'T'HH:mm:ss";

    const UNACCEPTABLE_TYPES = [
        MetadataControl::DOUBLE,
    ];

    const SUPPORTED_FILE_EXTENSIONS = [
        'txt',
    ];

    const SUPPORTED_ENCODING_TYPES = [
        'UTF-8',
    ];

    const NUMERIC_DETECTION_PARAM = 'numeric_detection';

    private function __construct() {
    }
}
