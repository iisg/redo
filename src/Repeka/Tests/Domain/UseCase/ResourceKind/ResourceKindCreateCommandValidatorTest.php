<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
use Repeka\Domain\Validation\Validator;

class ResourceKindCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var  MetadataCreateCommandValidator|PHPUnit_Framework_MockObject_MockObject */
    private $metadataCreateCommandValidator;

    /** @var  LanguageRepository|PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;

    /** @var ResourceKindCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new ResourceKindCreateCommandValidator($this->languageRepository, $this->metadataCreateCommandValidator);
        $this->metadataCreateCommandValidator->expects($this->any())->method('getValidator')->willReturn(Validator::alwaysValid());
    }

    public function testValidating() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['base_id' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['base_id' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailWhenNoLabel() {
        $command = new ResourceKindCreateCommand([], [
            ['base_id' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['base_id' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailWhenNoBaseIdForOneOfTheMetadata() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['base_id' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailWhenInvalidBaseId() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['base_id' => 'abc', 'name' => 'A', 'label' => ['PL' => 'L'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['base_id' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testPassesWhenInvalidMetadataBecauseItOnlyExtendsTheBase() {
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->validator = new ResourceKindCreateCommandValidator($this->languageRepository, $this->metadataCreateCommandValidator);
        $this->metadataCreateCommandValidator->expects($this->any())->method('getValidator')->willReturn(Validator::alwaysInvalid());
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['base_id' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['base_id' => 1, 'name' => 'B', 'label' => [], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailWhenNoMetadata() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], []);
        $this->validator->validate($command);
    }
}
