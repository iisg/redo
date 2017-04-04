<?php
namespace Repeka\Tests\Domain\UseCase\UserRole;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class UserRoleCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var UserRoleCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $languageRepositoryStub = $this->createLanguageRepositoryMock(['PL']);
        $this->validator = new UserRoleCreateCommandValidator(new NotBlankInAllLanguagesRule($languageRepositoryStub));
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
