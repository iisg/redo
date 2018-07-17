<?php
namespace Repeka\Domain\Cqrs;

interface CommandAuditor {
    public function beforeHandling(Command $command): ?array;

    public function afterHandling(Command $command, $result, ?array $beforeHandlingResult): ?array;

    public function afterError(Command $command, \Exception $exception, ?array $beforeHandlingResult): ?array;

    public function doSaveBeforeHandlingResult(): bool;
}
