<?php
namespace Repeka\Domain\Cqrs;

abstract class AbstractCommandAuditor implements CommandAuditor {
    public function beforeHandling(Command $command): ?array {
        return null;
    }

    public function afterHandling(Command $command, $result, ?array $beforeHandlingResult): ?array {
        return null;
    }

    public function afterError(Command $command, \Exception $exception, ?array $beforeHandlingResult): ?array {
        return null;
    }

    public function doSaveBeforeHandlingResult(): bool {
        return true;
    }
}
