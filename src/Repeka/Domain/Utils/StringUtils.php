<?php
namespace Repeka\Domain\Utils;

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
}
