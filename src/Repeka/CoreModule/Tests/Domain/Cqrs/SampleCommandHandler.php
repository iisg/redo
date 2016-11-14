<?php
namespace Repeka\CoreModule\Tests\Domain\Cqrs;

class SampleCommandHandler {
    public $lastCommand;

    public function handle(SampleCommand $command) {
        $this->lastCommand = $command;
    }
}
