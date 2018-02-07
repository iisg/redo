<?php
namespace Repeka\Domain\Cqrs;

interface Command {
    public function getCommandName();
}
