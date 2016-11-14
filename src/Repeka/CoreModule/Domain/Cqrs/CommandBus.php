<?php
namespace Repeka\CoreModule\Domain\Cqrs;

interface CommandBus {
    public function handle(Command $command);
}
