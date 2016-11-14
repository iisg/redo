<?php
namespace Repeka\CoreModule\Tests\Domain\Cqrs;

class CommandTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingCommandName() {
        $sampleCommand = new SampleCommand();
        $this->assertEquals('core.sample', $sampleCommand->getCommandName());
    }
}
