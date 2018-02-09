<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceKindCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MetadataCreateCommandValidator|PHPUnit_Framework_MockObject_MockObject */
    private $metadataCreateCommandValidator;
    /** @var  LanguageRepository|PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var ResourceKindCreateCommandValidator */
    private $validator;
    /** @var ResourceClassExistsRule|\PHPUnit_Framework_MockObject_MockObject */
    private $containsResourceClass;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceDisplayStrategyEvaluator */
    private $resourceDisplayStrategyEvaluator;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->containsResourceClass = $this->createMock(ResourceClassExistsRule::class);
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->resourceDisplayStrategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->validator = new ResourceKindCreateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepository),
            new CorrectResourceDisplayStrategySyntaxRule($this->resourceDisplayStrategyEvaluator),
            new ContainsParentMetadataRule()
        );
    }

    public function testValidating() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoLabel() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand([], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], []);
        $this->validator->validate($command);
    }

    public function testFailWhenOnlyParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [$this->createMetadataMock(SystemMetadata::PARENT)]);
        $this->validator->validate($command);
    }

    public function testFailWhenNoParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->createMetadataMock(),
            $this->createMetadataMock(),
        ]);
        $this->validator->validate($command);
    }

    public function testFailWhenDifferentResourceClassesOfMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
            $this->createMetadataMock(1, 1, null, [], 'unicorns'),
        ]);
        $this->validator->validate($command);
    }

    public function testIgnoringSystemMetadataResourceClass() {
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            SystemMetadata::PARENT()->toMetadata(),
            $this->createMetadataMock(),
        ]);
        $this->validator->validate($command);
    }

    public function testFailsWhenInvalidDisplayStrategy() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessage('incorrectResourceDisplayStrategy');
        $this->resourceDisplayStrategyEvaluator
            ->method('validateTemplate')
            ->with('This is the header')
            ->willThrowException(new InvalidResourceDisplayStrategyException('Syntax error'));
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
        ], ['header' => 'This is the header']);
        $this->validator->validate($command);
    }
}
