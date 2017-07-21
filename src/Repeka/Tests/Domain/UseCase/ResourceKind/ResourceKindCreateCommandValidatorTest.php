<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MetadataCreateCommandValidator|PHPUnit_Framework_MockObject_MockObject */
    private $metadataCreateCommandValidator;
    /** @var  LanguageRepository|PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var ResourceKindCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->validator = new ResourceKindCreateCommandValidator(new NotBlankInAllLanguagesRule($this->languageRepository));
    }

    public function testValidating() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoLabel() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand([], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoBaseIdForOneOfTheMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenInvalidBaseId() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 'abc', 'name' => 'A', 'label' => ['PL' => 'L'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testPassesWhenInvalidMetadataBecauseItOnlyExtendsTheBase() {
        $this->metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysInvalid());
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => [], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], []);
        $this->validator->validate($command);
    }

    public function testFailWithExplicitParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            [
                'baseId' => SystemMetadata::PARENT,
                'name' => 'A',
                'label' => ['PL' => 'Label A'],
                'description' => [],
                'placeholder' => [],
                'control' => 'text',
            ],
        ]);
        $this->validator->validate($command);
    }
}
