<?php
namespace Repeka\Domain\Cqrs;

interface CommandBus {
    public function handle(Command $command);
}
