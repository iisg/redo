<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommand;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommandValidator;

class MetadataDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataDeleteCommandValidator */
    private $validator;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->validator = new MetadataDeleteCommandValidator($this->metadataRepository);
    }

    public function testPositive() {
        $metadata = $this->createMock(Metadata::class);
        $command = new MetadataDeleteCommand($metadata);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfHasChildren() {
        $this->expectExceptionMessage('metadata kind has submetadata kinds');
        $metadata = $this->createMock(Metadata::class);
        $this->metadataRepository->method('countByParent')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }

    public function testInvalidIfUsedInResourceKinds() {
        $this->expectExceptionMessage('metadata kind is used in some resource kinds');
        $metadata = $this->createMock(Metadata::class);
        $this->metadataRepository->method('countByBase')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }

    public function testThrowsBothErrorsIfBothConditionsFail() {
        $this->expectExceptionMessage('metadata kind has submetadata kinds');
        $this->expectExceptionMessage('metadata kind is used in some resource kinds');
        $metadata = $this->createMock(Metadata::class);
        $this->metadataRepository->method('countByParent')->with($metadata)->willReturn(1);
        $this->metadataRepository->method('countByBase')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }
}
