<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommand;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class MetadataDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataDeleteCommandValidator */
    private $validator;
    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->validator = new MetadataDeleteCommandValidator($this->metadataRepository, $this->resourceKindRepository);
    }

    public function testPositive() {
        $metadata = $this->createMetadataMock();
        $command = new MetadataDeleteCommand($metadata);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfSystemMetadata() {
        $this->expectException(DomainException::class);
        $metadata = $this->createMetadataMock(-1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }

    public function testInvalidIfHasChildren() {
        $this->expectExceptionMessage('metadata kind has submetadata kinds');
        $metadata = $this->createMetadataMock();
        $this->metadataRepository->method('countByParent')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }

    public function testInvalidIfUsedInResourceKinds() {
        $this->expectExceptionMessage('metadata kind is used in some resource kinds');
        $metadata = $this->createMetadataMock();
        $this->resourceKindRepository->method('countByMetadata')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }

    public function testThrowsBothErrorsIfBothConditionsFail() {
        $this->expectExceptionMessage('metadata kind has submetadata kinds');
        $this->expectExceptionMessage('metadata kind is used in some resource kinds');
        $metadata = $this->createMetadataMock();
        $this->metadataRepository->method('countByParent')->with($metadata)->willReturn(1);
        $this->resourceKindRepository->method('countByMetadata')->with($metadata)->willReturn(1);
        $command = new MetadataDeleteCommand($metadata);
        $this->validator->validate($command);
    }
}
