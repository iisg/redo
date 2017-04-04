<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $languageRepositoryStub = $this->createMock(LanguageRepository::class);
        $languageRepositoryStub->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new MetadataUpdateCommandValidator(new ContainsOnlyAvailableLanguagesRule($languageRepositoryStub));
    }

    public function testPassingValidation() {
        $command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], []);
        $this->validator->validate($command);
    }

    public function testPassingValidationWhenNoEditAtAll() {
        $command = new MetadataUpdateCommand(1, [], [], []);
        $this->validator->validate($command);
    }

    public function testFailingBecauseOfWrongMetadataId() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataUpdateCommand(0, [], [], []);
        $this->validator->validate($command);
    }

    public function testFailingBecauseOfInvalidLanguage() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataUpdateCommand(1, ['X' => 'bad'], [], []);
        $this->validator->validate($command);
    }
}
