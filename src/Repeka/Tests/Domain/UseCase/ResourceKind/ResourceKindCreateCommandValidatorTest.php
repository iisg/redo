<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceKindCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindCreateCommandValidator */
    private $validator;
    /** @var NotBlankInAllLanguagesRule|\PHPUnit_Framework_MockObject_MockObject */
    private $notBlankInAllLanguagesRule;
    /** @var ContainsParentMetadataRule|\PHPUnit_Framework_MockObject_MockObject */
    private $containsParentMetadataRule;
    /** @var MetadataUpdateCommandValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataUpdateCommandValidator;
    /** @var ChildResourceKindsAreOfSameResourceClassRule|\PHPUnit_Framework_MockObject_MockObject */
    private $childResourceKindsAreOfSameResourceClassRule;
    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var MetadataUpdateCommandAdjuster|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataUpdateCommandAdjuster;

    protected function setUp() {
        $this->metadataUpdateCommandAdjuster = $this->createMock(MetadataUpdateCommandAdjuster::class);
        $this->metadataUpdateCommandAdjuster->method('adjustCommand')->willReturnArgument(0);
        $this->metadataUpdateCommandValidator = $this->createMock(MetadataUpdateCommandValidator::class);
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->resourceKindRepository->method('countByQuery')->willReturn(0);
        $this->notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $this->containsParentMetadataRule = $this->createRuleMock(ContainsParentMetadataRule::class, true);
        $this->childResourceKindsAreOfSameResourceClassRule = $this->createRuleMock(
            ChildResourceKindsAreOfSameResourceClassRule::class,
            true
        );
        $this->validator = new ResourceKindCreateCommandValidator(
            $this->notBlankInAllLanguagesRule,
            $this->containsParentMetadataRule,
            $this->metadataUpdateCommandAdjuster,
            $this->metadataUpdateCommandValidator,
            $this->childResourceKindsAreOfSameResourceClassRule,
            $this->resourceKindRepository
        );
    }

    public function testValidating() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $command = new ResourceKindCreateCommand(
            'label',
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(SystemMetadata::REPRODUCTOR),
                $this->createMetadataMock(SystemMetadata::RESOURCE_LABEL),
                $this->createMetadataMock(),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailWhenEmptyName() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(
            '',
            ['EN' => 'Label'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailWhenNameAlreadyExists() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $resourceKindRepository->method('countByQuery')->willReturn(1);
        $validator = new ResourceKindCreateCommandValidator(
            $this->notBlankInAllLanguagesRule,
            $this->containsParentMetadataRule,
            $this->metadataUpdateCommandAdjuster,
            $this->metadataUpdateCommandValidator,
            $this->childResourceKindsAreOfSameResourceClassRule,
            $resourceKindRepository
        );
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(
            'existing',
            ['EN' => 'Label'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
            ]
        );
        $validator->validate($command);
    }
}
