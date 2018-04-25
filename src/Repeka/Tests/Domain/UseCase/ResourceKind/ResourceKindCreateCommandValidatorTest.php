<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Exception\InvalidResourceDisplayStrategyException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
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
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRepository;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->containsResourceClass = $this->createMock(ResourceClassExistsRule::class);
        $this->metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $this->metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->resourceDisplayStrategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->resourceKindRepository->method('findOne')->will($this->returnValueMap([
            [1, $this->createResourceKindMock(1, 'books')],
            [2, $this->createResourceKindMock(2, 'dictionaries')],
        ]));
        $this->validator = new ResourceKindCreateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepository),
            new CorrectResourceDisplayStrategySyntaxRule($this->resourceDisplayStrategyEvaluator),
            new ContainsParentMetadataRule(),
            new ChildResourceKindsAreOfSameResourceClassRule($this->resourceKindRepository)
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

    public function testFailsWhenChildrenHaveDifferentResourceClasses() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT, null, null, ['resourceKind' => [1,2]]),
            $this->createMetadataMock(0, null, null, [], 'books'),
        ]);
        $this->validator->validate($command);
    }
}
