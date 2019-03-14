<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;

/**
 * @method static FileUploaderType SIMPLE()
 * @method static FileUploaderType FILE_MANAGER()
 */
class FileUploaderType extends Enum {
    const SIMPLE = 'simple';
    const FILE_MANAGER = 'file_manager';
}
