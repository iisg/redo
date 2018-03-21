<?php
namespace Repeka\Domain\Cqrs;

abstract class AbstractCommandAuditor implements CommandAuditor {
    public function beforeHandling(Command $command): ?array {
        return null;
    }

    public function afterHandling(Command $command, $result): ?array {
        return null;
    }

    public function afterError(Command $command, \Exception $exception): ?array {
        return null;
    }
}
