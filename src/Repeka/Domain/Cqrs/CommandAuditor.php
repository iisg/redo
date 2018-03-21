<?php
namespace Repeka\Domain\Cqrs;

interface CommandAuditor {
    public function beforeHandling(Command $command): ?array;

    public function afterHandling(Command $command, $result): ?array;

    public function afterError(Command $command, \Exception $exception): ?array;
}
