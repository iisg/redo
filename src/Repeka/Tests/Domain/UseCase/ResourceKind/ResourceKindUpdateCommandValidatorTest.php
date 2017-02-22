<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandValidator;
use Repeka\Domain\Validation\Validator;

class ResourceKindUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var  MetadataCreateCommandValidator|PHPUnit_Framework_MockObject_MockObject */
    private $metadataCreateCommandValidator;

    /** @var  LanguageRepository|PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;

    /** @var ResourceKindUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new ResourceKindUpdateCommandValidator($this->languageRepository, $this->metadataCreateCommandValidator);
        $this->metadataCreateCommandValidator->expects($this->any())->method('getValidator')->willReturn(Validator::alwaysValid());
    }

    public function testValidating() {
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }

    public function testInvalidWhereInvalidId() {
        $command = new ResourceKindUpdateCommand(0, ['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->expectException(InvalidCommandException::class);
        $this->validator->validate($command);
    }
}
