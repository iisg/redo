<?php
namespace Repeka\Tests\Domain\Domain\Cqrs;

use Repeka\Tests\Domain\Cqrs\SampleCommand;

class CommandTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingCommandName() {
        $sampleCommand = new SampleCommand();
        $this->assertEquals('sample', $sampleCommand->getCommandName());
    }
}
