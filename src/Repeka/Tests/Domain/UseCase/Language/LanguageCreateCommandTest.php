<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\UseCase\Language\LanguageCreateCommand;

class LanguageCreateCommandTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingFromArray() {
        $createCommand = LanguageCreateCommand::fromArray([
            'flag' => 'PL',
            'name' => 'polski',
        ]);
        $this->assertEquals($createCommand->getFlag(), 'PL');
        $this->assertEquals($createCommand->getName(), 'polski');
    }
}
