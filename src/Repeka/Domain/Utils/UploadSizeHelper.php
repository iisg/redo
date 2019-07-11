<?php
namespace Repeka\Domain\Utils;

final class UploadSizeHelper {
    private function __construct() {
    }

    public static function getMaxUploadSizePerFile(): int {
        $perFileLimit = 1024 * (int)ini_get('upload_max_filesize') * (substr(ini_get('upload_max_filesize'), -1) == 'M' ? 1024 : 1);
        return min($perFileLimit, self::getMaxUploadSize());
    }

    public static function getMaxUploadSize(): int {
        return 1024 * (int)ini_get('post_max_size') * (substr(ini_get('post_max_size'), -1) == 'M' ? 1024 : 1);
    }

    /** @see https://stackoverflow.com/a/37523842/878514 */
    public static function formatBytes($bytes, int $precision = 1): string {
        if ($bytes instanceof ImmutableIteratorAggregate) {
            $bytes = $bytes->toArray();
        }
        if (is_array($bytes)) {
            return new PrintableArray(
                array_map(
                    function ($bytes) use ($precision) {
                        return $this->formatBytes($bytes, $precision);
                    },
                    $bytes
                )
            );
        } else {
            $units = ['B', 'kB', 'MB', 'GB', 'TB'];
            $bytes = max((string)$bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }
}
