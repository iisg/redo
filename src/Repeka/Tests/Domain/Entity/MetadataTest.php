<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingMetadata() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA']);
        $this->assertEquals('text', $metadata->getControl());
        $this->assertEquals('Prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
    }

    public function testCreatingForResourceKind() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertSame($rk, $childMetadata->getResourceKind());
    }

    public function testGettingControlAndNameOfBaseMetadata() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals('text', $childMetadata->getControl());
        $this->assertEquals('Prop', $childMetadata->getName());
    }

    public function testExtendingLanguageValues() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
        $this->assertEquals('EN', $childMetadata->getLabel()['EN']);
    }

    public function testOverridingLanguageValues() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => 'Another'], $rk, $baseMetadata);
        $this->assertEquals('Another', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenEmpty() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenBlank() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => '   '], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }
}
