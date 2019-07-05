<?php
namespace Repeka\Application\Service;

use Assert\Assertion;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Utils\StringUtils;

class RealFileSystemDriver implements FileSystemDriver {
    public function mkdirRecursive(string $path): void {
        if (!file_exists($path)) {
            $result = mkdir($path, 0750, true);
            Assertion::true($result, 'Could not create directory: ' . $path);
        }
    }

    public function putContents(string $path, string $contents): void {
        $directory = dirname($path);
        $this->mkdirRecursive($directory);
        $bytesWritten = file_put_contents($path, $contents);
        Assertion::true($bytesWritten !== false, 'Could not write to file: ' . $path);
    }

    public function copy(string $source, string $destination): void {
        $this->mkdirRecursive(dirname($destination));
        $copied = copy($source, $destination);
        Assertion::true($copied, "Could not copy file $source to $destination");
    }

    public function listDirectory(string $path, int $sort = SCANDIR_SORT_ASCENDING): array {
        if (!file_exists($path) || !is_dir($path)) {
            return [];
        }
        $contents = scandir($path, $sort);
        Assertion::isArray($contents, 'Could not read the directory ' . $path);
        return array_values(array_diff($contents, ['.', '..']));
    }

    public function glob(string $pattern): array {
        return array_map(StringUtils::class . '::unixSlashes', glob(StringUtils::unixSlashes($pattern), GLOB_MARK | GLOB_BRACE));
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function delete(string $path): void {
        if (is_dir($path)) {
            $this->deleteDirectoryContents($path);
            Assertion::true(rmdir($path), 'Could not delete directory: ' . $path);
        } elseif ($this->exists($path)) {
            Assertion::true(unlink($path), 'Could not delete file: ' . $path);
        }
    }

    /** @see https://stackoverflow.com/a/3349792/878514 */
    public function deleteDirectoryContents(string $path): void {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $this->delete($file->getRealPath());
        }
    }

    public function getImageDimensions(string $path): array {
        $exif = @exif_read_data($path, 'COMPUTED');
        if ($exif !== false) {
            $width = $exif['COMPUTED']['Width'];
            $height = $exif['COMPUTED']['Height'];
        } else {
            [$width, $height,] = getimagesize($path);
        }
        return ['width' => $width, 'height' => $height];
    }

    public function getFileSize(string $path): int {
        if (is_dir($path)) {
            $size = 0;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $size += $object->getSize();
            }
            return $size;
        } else {
            Assertion::integer($size = filesize($path), 'Could not read the filesize of: ' . $path);
            return $size;
        }
    }
}
