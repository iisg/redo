<?php
namespace Repeka\Domain\Exception;

class DomainException extends \RuntimeException {
    private $errorMessageId;
    private $params;

    /**
     * @param string $errorMessageId Used in front-end to build localized, user-friendly error messages
     * @param array $params Parameters that front-end can plug into localized error messages
     */
    public function __construct(
        string $errorMessageId,
        int $code = 400,
        array $params = [],
        \Exception $previous = null,
        ?string $message = null
    ) {
        parent::__construct($message ?: "Domain exception '$errorMessageId' (error $code)", $code, $previous);
        $this->errorMessageId = $errorMessageId;
        $this->params = $params;
    }

    public function getErrorMessageId(): string {
        return $this->errorMessageId;
    }

    public function getParams(): array {
        return $this->params;
    }
}
