<?php
namespace Repeka\Application\Upload;

class FilesystemDriver {
    public function move(string $source, string $target) {
        $result = rename($source, $target); // yes, that's how you move files in PHP
        if (!$result) {
            throw new FilesystemOperationException(__METHOD__, func_get_args());
        }
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function mkdirRecursive(string $path, int $mode) {
        $result = mkdir($path, $mode, true);
        if (!$result) {
            throw new FilesystemOperationException(__METHOD__, func_get_args());
        }
    }
}
