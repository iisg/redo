<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $languageRepository = $this->createMock(LanguageRepository::class);
        $languageRepository->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $notBlankInAllLanguagesRule = $this->createMock(NotBlankInAllLanguagesRule::class);
        $this->validator = new ResourceKindUpdateCommandValidator($notBlankInAllLanguagesRule);
    }

    public function testValid() {
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [],
                'control' => 'relationship', 'constraints' => ['resourceKind' => [123]]],
        ]);
        $this->validator->validate($command);
    }

    public function testInvalidWhereInvalidId() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(0, ['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->validator->validate($command);
    }
}
