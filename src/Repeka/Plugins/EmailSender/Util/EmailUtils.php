<?php
namespace Repeka\Plugins\EmailSender\Util;

final class EmailUtils {
    private function __construct() {
    }

    public static function getValidEmailAddresses(string $emails): array {
        return array_values(
            array_unique(
                array_map(
                    'trim',
                    array_filter(
                        explode(',', $emails),
                        function ($email) {
                            return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
                        }
                    )
                )
            )
        );
    }
}
