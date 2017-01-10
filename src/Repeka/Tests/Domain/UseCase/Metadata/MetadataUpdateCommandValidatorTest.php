<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $repository = $this->createMock(LanguageRepository::class);
        $repository->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new MetadataUpdateCommandValidator($repository);
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

    public function testFailingBecauseOfInvalidLanuage() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataUpdateCommand(1, ['X' => 'bad'], [], []);
        $this->validator->validate($command);
    }
}
