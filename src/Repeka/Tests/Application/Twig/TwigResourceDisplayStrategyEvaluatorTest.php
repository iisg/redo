<?php
namespace Repeka\Tests\Application\Upload;

use Psr\Container\ContainerInterface;
use Repeka\Application\Twig\TwigResourceDisplayStrategyEvaluator;
use Repeka\Tests\Traits\StubsTrait;
use Twig\Environment;
use Twig\Template;

class TwigResourceDisplayStrategyEvaluatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var TwigResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;
    /** @var Template|\PHPUnit_Framework_MockObject_MockObject */
    private $template;

    /** @before */
    public function init() {
        $env = $this->createMock(Environment::class);
        $this->template = $this->createMock(Template::class);
        $env->method('createTemplate')->willReturn($this->template);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($env);
        $this->displayStrategyEvaluator = new TwigResourceDisplayStrategyEvaluator($container);
    }

    public function testSimpleValueAsMetadataValue() {
        $this->template->method('render')->willReturn('Unicorn');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(1, $values);
        $this->assertEquals('Unicorn', $values[0]->getValue());
    }

    public function testArrayOfValues() {
        $this->template->method('render')->willReturn('["Unicorn", "Rainbow"]');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(2, $values);
        $this->assertEquals('Unicorn', $values[0]->getValue());
        $this->assertEquals('Rainbow', $values[1]->getValue());
    }

    public function testValueWithSubmetadata() {
        $this->template->method('render')->willReturn('{"value": "Unicorn", "submetadata": {"5": "Rainbow"}}');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(1, $values);
        $this->assertEquals('Unicorn', $values[0]->getValue());
        $this->assertCount(1, $values[0]->getSubmetadata());
        $this->assertEquals(['Rainbow'], $values[0]->getSubmetadata(5));
    }

    public function testObjectAsMetadataValue() {
        $this->template->method('render')->willReturn('{"from": "2013-01-01"}');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(1, $values);
        $this->assertEquals(['from' => '2013-01-01'], $values[0]->getValue());
        $this->assertEmpty($values[0]->getSubmetadata());
    }

    public function testArrayOfValuesWithSubmetadata() {
        $this->template->method('render')
            ->willReturn('[{"value": "Unicorn", "submetadata": {"5": "Rainbow"}},{"value": "Bocian", "submetadata": {"6": "Klekle"}}]');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(2, $values);
        $this->assertEquals('Unicorn', $values[0]->getValue());
        $this->assertCount(1, $values[0]->getSubmetadata());
        $this->assertEquals(['Rainbow'], $values[0]->getSubmetadata(5));
        $this->assertEquals('Bocian', $values[1]->getValue());
        $this->assertCount(1, $values[1]->getSubmetadata());
        $this->assertEquals(['Klekle'], $values[1]->getSubmetadata(6));
    }

    public function testInvalidJson() {
        $this->template->method('render')->willReturn('["Unicorn", "Rainbow"');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(1, $values);
        $this->assertEquals('["Unicorn", "Rainbow"', $values[0]->getValue());
    }

    public function testEmptyStringToEmptyArray() {
        $this->template->method('render')->willReturn('');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertEmpty($values);
    }

    public function testEmptyArrayToEmptyArray() {
        $this->template->method('render')->willReturn('[]');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertEmpty($values);
    }

    public function testBlankStringToEmptyArray() {
        $this->template->method('render')->willReturn('   ');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertEmpty($values);
    }

    public function testTrimming() {
        $this->template->method('render')->willReturn('  a ');
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(1, $values);
        $this->assertEquals('a', $values[0]->getValue());
    }

    public function testCanHaveExtraCommaAtTheEnd() {
        $this->template->method('render')->willReturn("[1,\n2,\n3,\n]");
        $values = $this->displayStrategyEvaluator->renderToMetadataValues(null, '');
        $this->assertCount(3, $values);
        $this->assertEquals('1', $values[0]->getValue());
        $this->assertEquals('2', $values[1]->getValue());
        $this->assertEquals('3', $values[2]->getValue());
    }
}
