<?php
namespace Repeka\Application\Upload;

use Repeka\Domain\Exception\DomainException;

class FilesystemDriver {
    public function move(string $source, string $target) {
        $this->mkdirRecursive(dirname($target));
        $result = rename($source, $target); // yes, that's how you move files in PHP
        if (!$result) {
            throw new FilesystemOperationException(__METHOD__, func_get_args());
        }
    }

    public function listFiles(string $path): array {
        $allContent = scandir($path);
        if (!$allContent) {
            throw new DomainException("Scaning folder exception");
        }
        $fileList = []; //choose only files
        foreach ($allContent as $file) {
            if (is_file($path . $file)) {
                $fileList[] = $file;
            }
        }
        return $fileList;
    }

    public function delete(string $filepath): void {
        $isUnlinked = unlink($filepath);
        if (!$isUnlinked) {
            throw new FilesystemOperationException(__METHOD__, func_get_args());
        }
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function mkdirRecursive(string $path, int $mode = 0750) {
        $mode = 0750;
        if (!file_exists($path)) {
            $result = mkdir($path, $mode, true);
            if (!$result) {
                throw new FilesystemOperationException(__METHOD__, func_get_args());
            }
        }
    }
}
