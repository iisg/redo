<?php
namespace Repeka\Domain\XmlImport\Transform;

class TransformLoaderTest extends \PHPUnit_Framework_TestCase {
    /** @var RegexReplaceTransform|\PHPUnit_Framework_MockObject_MockObject */
    private $regexReplaceTransform;
    /** @var JoinTransform|\PHPUnit_Framework_MockObject_MockObject */
    private $joinTransform;

    /** @var TransformLoader */
    private $loader;

    protected function setUp() {
        $this->regexReplaceTransform = $this->createMock(RegexReplaceTransform::class);
        $this->regexReplaceTransform->method('forArguments')->willReturnSelf();
        $this->joinTransform = $this->createMock(JoinTransform::class);
        $this->joinTransform->method('forArguments')->willReturnSelf();
        $this->loader = new TransformLoader($this->regexReplaceTransform, $this->joinTransform);
    }

    public function testLoadsVariousTransforms() {
        $input = [
            'regex' => ['regex' => '.*', 'replacement' => ''],
            'join' => ['glue' => '~'],
        ];
        $result = $this->loader->load($input);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(RegexReplaceTransform::class, $result['regex']);
        $this->assertInstanceOf(JoinTransform::class, $result['join']);
    }

    public function testRejectsInvalidKeySet() {
        $this->expectException(InvalidTransformException::class);
        $input = [
            'myKey' => ['regex' => '.*', 'replacement' => '', 'extra' => null],
        ];
        $this->loader->load($input);
    }
}
