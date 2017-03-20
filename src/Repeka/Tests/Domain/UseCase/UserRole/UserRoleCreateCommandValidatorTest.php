<?php
namespace Repeka\Tests\Domain\UseCase\UserRole;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommandValidator;

class UserRoleCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var UserRoleCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $repository = $this->createMock(LanguageRepository::class);
        $repository->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new UserRoleCreateCommandValidator($repository);
    }

    public function testPassingValidation() {
        $command = new UserRoleCreateCommand(['PL' => 'Test']);
        $this->validator->validate($command);
    }

    public function testFailsValidationBecauseOfInvalidName() {
        $this->expectException(InvalidCommandException::class);
        $command = new UserRoleCreateCommand([]);
        $this->validator->validate($command);
    }
}
