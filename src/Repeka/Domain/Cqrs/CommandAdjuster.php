<?php
namespace Repeka\Domain\Cqrs;

interface CommandAdjuster {
    public function adjustCommand(Command $command): Command;
}
