<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\Rules\ResourceDoesNotContainDuplicatedFilenamesRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceDoesNotContainDuplicatedFilenamesRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var ResourceDoesNotContainDuplicatedFilenamesRule */
    private $rule;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->metadataRepository->method('findByQuery')->willReturn([
            $this->createMetadataMock(1),
            $this->createMetadataMock(2), //it returns all file metadata
            $this->createMetadataMock(4),
        ]);
        $this->rule = new ResourceDoesNotContainDuplicatedFilenamesRule($this->metadataRepository);
    }

    public function testNoFilesPassesValidation() {
        $this->assertTrue($this->rule->validate(ResourceContents::empty()));
    }

    public function testNoDuplicatesPassingValidation() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'c.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['d.txt', 'e.txt'],
        ]);
        $this->assertTrue($this->rule->validate($resourceContents));
    }

    public function testCaseInsensitive() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'A.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['B.txt', 'C.txt'],
        ]);
        $this->assertTrue($this->rule->validate($resourceContents));
    }

    public function testOneDuplicateFailingValidation() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'b.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['d.txt', 'e.txt'],
        ]);
        $this->assertFalse($this->rule->validate($resourceContents));
    }

    public function testMultipleDuplicationsFailingValidation() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt', 'c.txt'],
            2 => 'a.txt',
            3 => 'c.txt', // non-file metadata id so no error
            4 => ['b.txt', 'a.txt'],
        ]);
        $this->assertFalse($this->rule->validate($resourceContents));
    }

    public function testOneMultiplicatedFileFailingValidation() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'a.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['c.txt', 'a.txt'],
        ]);
        $this->assertFalse($this->rule->validate($resourceContents));
    }

    public function testTellsWhichItemsAreDuplicated() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'a.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['b.txt', 'a.txt'],
        ]);
        try {
            $this->rule->assert($resourceContents);
            $this->fail('The line above should throw an exception.');
        } catch (DomainException $e) {
            $params = implode($e->getParams());
            $this->assertContains('a.txt', $params);
            $this->assertContains('b.txt', $params);
        }
    }

    public function testTellsAboutMultiplicatedFileOnlyOnce() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'a.txt',
            3 => 'c.txt', // non-file metadata id so no error
            4 => ['c.txt', 'a.txt'],
        ]);
        try {
            $this->rule->assert($resourceContents);
            $this->fail('The line above should throw an exception.');
        } catch (DomainException $e) {
            $params = implode($e->getParams());
            $this->assertContains('a.txt', $params);
            $params = str_replace("a.txt", "", $params);
            $this->assertNotContains('a.txt', $params);
        }
    }

    public function testCheckingException() {
        $resourceContents = ResourceContents::fromArray([
            1 => ['a.txt', 'b.txt'],
            2 => 'a.txt',
            3 => 'a.txt', // non-file metadata id so no error
            4 => ['c.txt', 'a.txt'],
        ]);
        $this->expectException(DomainException::class);
        $this->rule->assert($resourceContents);
    }
}
