<?php
namespace Repeka\Domain\Service;

interface FileSystemDriver {
    public function mkdirRecursive(string $path): void;

    public function putContents(string $path, string $contents): void;

    public function copy(string $source, string $destination): void;

    public function listDirectory(string $path): array;

    public function glob(string $pattern): array;

    public function exists(string $path): bool;

    public function deleteDirectoryContents(string $path): void;

    public function delete(string $path): void;
}
