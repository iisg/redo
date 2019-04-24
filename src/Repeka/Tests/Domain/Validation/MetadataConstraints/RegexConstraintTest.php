<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Application\Service\PhpRegexNormalizer;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;
use Repeka\Tests\Traits\StubsTrait;

class RegexConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var RegexConstraint */
    private $constraint;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $this->constraint = new RegexConstraint(new PhpRegexNormalizer());
        $this->resource = $this->createResourceMock(1);
    }

    public function testRejectsBadArguments() {
        $this->assertFalse($this->constraint->isConfigValid(['bad']));
        $this->assertFalse($this->constraint->isConfigValid(0));
        $this->assertFalse($this->constraint->isConfigValid(null));
    }

    public function testRejectInvalidRegexWithExceptionParamToTranslate() {
        try {
            $this->assertFalse($this->constraint->isConfigValid(')'));
        } catch (InvalidCommandException $e) {
            $array = [
                'field' => 'constraints',
                'constraintError' => 'invalidRegex',
                DomainException::TRANSLATE_PARAMS => ['constraintError'],
            ];
            $this->assertEquals($e->getViolations(), $array);
        }
    }

    public function testAcceptsGoodArguments() {
        $this->assertTrue($this->constraint->isConfigValid(''));
        $this->assertTrue($this->constraint->isConfigValid('ok'));
    }

    /**
     * @dataProvider regexTestCases
     */
    public function testValidates($regex, $value, $shouldBeValid) {
        if (!$shouldBeValid) {
            $this->expectException(InvalidCommandException::class);
        }
        $this->constraint->validateSingle($this->createMetadataMock(1, null, null, ['regex' => $regex]), $value, $this->resource);
    }

    public function regexTestCases() {
        return [
            ['^a', null, true],
            ['^a', '', true],
            ['^a', 'aXb', true],
            ['b$', 'aXb', true],
            ['', 'whatever', true],
            ['', '', true],
            ['^$', '', true],
            ['^abc$', 'abc', true],
            ['d.f', 'abcdefgh', true],
            ['b$', 'aXB', false],
            ['^abc$', 'xabcx', false],
            // ['a\\/b', 'a\\/b', true],
            ['a\\/b', 'a\\\\/b', false],
            ['a\\/b', 'a\\//b', false],
        ];
    }

    public function testFailsIfOneValueInArrayIsIncorrect() {
        $this->expectException(InvalidCommandException::class);
        $this->constraint->validateAll(
            $this->createMetadataMock(1, null, null, ['regex' => '^a']),
            [['value' => 'abc'], ['value' => 'bcd']],
            $this->resource
        );
    }
}
