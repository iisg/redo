<?php
namespace Repeka\Domain\Exception;

class InvalidResourceDisplayStrategyException extends DomainException {
    public function __construct(string $errorMessage, \Exception $previous = null) {
        parent::__construct(
            'invalidResourceDisplayStrategy',
            400,
            [
                'message' => $errorMessage,
            ],
            $previous,
            $errorMessage
        );
    }
}
