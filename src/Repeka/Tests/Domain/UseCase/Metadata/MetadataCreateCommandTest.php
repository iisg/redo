<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataCreateCommandTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingFromArray() {
        $createCommand = MetadataCreateCommand::fromArray([
            'name' => 'nazwa',
            'label' => ['PL' => 'Labelka'],
            'placeholder' => [],
            'description' => [],
            'control' => 'text',
        ]);
        $this->assertEquals($createCommand->getName(), 'nazwa');
        $this->assertEquals($createCommand->getLabel()['PL'], 'Labelka');
        $this->assertEmpty($createCommand->getPlaceholder());
        $this->assertEmpty($createCommand->getDescription());
        $this->assertEquals('text', $createCommand->getControlName());
    }
}
