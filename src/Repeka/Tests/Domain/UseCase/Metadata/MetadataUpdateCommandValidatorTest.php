<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepositoryStub;
    /** @var ConstraintArgumentsAreValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintArgumentsAreValid;
    /** @var MetadataUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->languageRepositoryStub = $this->createMock(LanguageRepository::class);
        $this->languageRepositoryStub->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->constraintArgumentsAreValid = $this->createMock(ConstraintArgumentsAreValidRule::class);
        $this->validator = new MetadataUpdateCommandValidator(
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new ConstraintSetMatchesControlRule($this->createMock(MetadataRepository::class)),
            $this->constraintArgumentsAreValid
        );
    }

    public function testPassesWithoutResourceKindConstraint() {
        $command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], [], []);
        $this->validator->validate($command);
    }

    public function testPassingValidationWhenNoEditAtAll() {
        $command = new MetadataUpdateCommand(1, [], [], [], []);
        $this->validator->validate($command);
    }

    public function testFailsWithWrongMetadataId() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataUpdateCommand(0, [], [], [], []);
        $this->validator->validate($command);
    }

    public function testFailsWithInvalidLanguage() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataUpdateCommand(1, ['X' => 'bad'], [], [], []);
        $this->validator->validate($command);
    }

    public function testPassesWithValidResourceKindConstraint() {
        $metadata = $this->createMetadataMock(1, null, 'relationship');
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [1 => $metadata]);
        $this->constraintArgumentsAreValid->method('validate')->willReturn(true);
        $validator = new MetadataUpdateCommandValidator(
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new ConstraintSetMatchesControlRule($metadataRepository),
            $this->constraintArgumentsAreValid
        );
        $command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], [], [
            'resourceKind' => [0]
        ]);
        $validator->validate($command);
    }

    public function testFailsForRelationshipWithoutResourceKindConstraint() {
        $this->expectException(InvalidCommandException::class);
        $metadata = $this->createMetadataMock(1, null, 'relationship');
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [1 => $metadata]);
        $this->constraintArgumentsAreValid->expects($this->never())->method('validate');
        $validator = new MetadataUpdateCommandValidator(
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new ConstraintSetMatchesControlRule($metadataRepository),
            $this->constraintArgumentsAreValid
        );
        $command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], [], []);
        $validator->validate($command);
    }
}
