<?php
namespace Repeka\Domain\Exception;

class DomainException extends \RuntimeException {
    private $data = [];

    public function __construct(string $message, \Exception $previous = null) {
        parent::__construct($message, 400, $previous);
    }

    public function getData(): array {
        return $this->data;
    }

    public function setData(array $data): DomainException {
        $this->data = $data;
        return $this;
    }

    public function setCode(int $code): DomainException {
        $this->code = $code;
        return $this;
    }
}
