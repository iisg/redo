<?php
namespace Repeka\Domain\Utils;

use Cocur\Slugify\Slugify;

final class StringUtils {
    private function __construct() {
    }

    /**
     * @see https://stackoverflow.com/a/15575293/878514
     */
    public static function joinPaths(...$paths): string {
        $nonEmptyPaths = array_filter(
            $paths,
            function ($s) {
                return $s !== '';
            }
        );
        return preg_replace('#/+#', '/', join('/', $nonEmptyPaths));
    }

    public static function normalizeEntityName(string $name): string {
        $unCamelCased = preg_replace('#([a-z])([A-Z])#', '$1 $2', $name);
        return (new Slugify(['separator' => '_']))->slugify($unCamelCased);
    }
}
