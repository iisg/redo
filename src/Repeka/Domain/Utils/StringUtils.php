<?php
namespace Repeka\Domain\Utils;

use Cocur\Slugify\Slugify;

final class StringUtils {
    private function __construct() {
    }

    /**
     * @see https://stackoverflow.com/a/15575293/878514
     */
    public static function joinPaths(string...$paths): string {
        $nonEmptyPaths = array_filter(
            $paths,
            function ($s) {
                return $s !== '' && $s !== '.';
            }
        );
        return self::unixSlashes(preg_replace('#/+#', '/', join('/', $nonEmptyPaths)));
    }

    public static function unixSlashes(?string $path): ?string {
        if (!$path) {
            return $path;
        }
        return str_replace('\\', '/', $path);
    }

    public static function normalizeEntityName(string $name): string {
        $unCamelCased = preg_replace('#([a-z])([A-Z])#', '$1 $2', $name);
        return (new Slugify(['separator' => '_']))->slugify($unCamelCased);
    }

    /**
     * @see https://stackoverflow.com/a/8215387/878514
     */
    public static function fixUtf8(string $string): string {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    /** @see http://stackoverflow.com/a/19533226/878514 */
    public static function toSnakeCase($camelCase) {
        return strtolower(preg_replace('/(?<!^)[A-Z]+/', '_$0', $camelCase));
    }
}
