<?php
namespace Repeka\Application\Command;

class DirectoryContentsLister {
    /** @return \SplFileInfo[] */
    public function listRecursively(string $path): array {
        if (!is_dir($path)) {
            return [];
        }
        $directoryIterator = new \RecursiveDirectoryIterator(
            $path,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $flatDirectoryIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST, // useful for deleting folder trees recursively
            \RecursiveIteratorIterator::CATCH_GET_CHILD // ignore permission errors
        );
        return iterator_to_array($flatDirectoryIterator);
    }

    /** @return \SplFileInfo[] */
    public function listSubfoldersRecursively(string $path): array {
        return array_filter($this->listRecursively($path), function (\SplFileInfo $fileInfo) {
            return $fileInfo->isDir();
        });
    }
}
