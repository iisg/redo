<?php
namespace Repeka\Application\Authentication;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PKAuthenticationException extends AuthenticationException {
    const PASSWORD_REDACTED = '[password redacted]';

    private $data;

    /** @param mixed $data */
    public function __construct(string $message, $data) {
        parent::__construct($message);
        $this->data = $this->removeSensitiveData($data);
    }

    public function getData() {
        return $this->data;
    }

    public function __toString() {
        $output = parent::__toString();
        $dataString = var_export($this->data, true);
        return "$output\nAdditional data: $dataString";
    }

    private function removeSensitiveData($data) {
        // array_walk_recursive *does* walk on objects without any warnings, but not recursively and values by reference don't work.
        // We have to re-invent it to work with both arrays and objects, recursive and possible intermixed.
        if (is_array($data) || is_object($data)) {
            array_walk($data, function (&$value, $keyOrField) use (&$data) {
                $replacement = null;
                if ($this->containsSensitiveValue($keyOrField)) {
                    $replacement = self::PASSWORD_REDACTED;
                } elseif (is_array($value) || is_object($value)) {
                    $replacement = $this->removeSensitiveData($value);
                }
                if ($replacement !== null) {
                    if (is_array($data)) {
                        $data[$keyOrField] = $replacement;
                    } else { // $data is an object
                        $data->$keyOrField = $replacement;
                    }
                }
            });
        }
        return $data;
    }

    private function containsSensitiveValue($key):bool {
        return strpos(strtolower($key), 'password') !== false;
    }
}
