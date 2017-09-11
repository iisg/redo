<?php
namespace Repeka\Application\Upload;

class UploadSizeHelper {
    public function getMaxUploadSizePerFile(): int {
        $perFileLimit = 1024 * (int)ini_get('upload_max_filesize') * (substr(ini_get('upload_max_filesize'), -1) == 'M' ? 1024 : 1);
        return min($perFileLimit, $this->getMaxUploadSize());
    }

    public function getMaxUploadSize(): int {
        return 1024 * (int)ini_get('post_max_size') * (substr(ini_get('post_max_size'), -1) == 'M' ? 1024 : 1);
    }
}
