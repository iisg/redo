<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var UserLoaderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $userLoader;
    /** @var UserCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->validator = new UserCreateCommandValidator($this->userLoader, new ResourceContentsCorrectStructureRule());
    }

    public function testAcceptsValidUsername() {
        $command = new UserCreateCommand('JohnDoe');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testRejectsEmptyUsername() {
        $command = new UserCreateCommand('');
        $this->userLoader->expects($this->never())->method('loadUserByUsername');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testRejectsInvalidContentsStructure() {
        $command = new UserCreateCommand('John', 'bla', new ResourceContents(['a']));
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testRejectsBlankUsername() {
        $command = new UserCreateCommand(' ');
        $this->userLoader->expects($this->never())->method('loadUserByUsername');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testRejectsTakenUsername() {
        $command = new UserCreateCommand('JohnDoe');
        $this->userLoader->expects($this->once())->method('loadUserByUsername')->willReturn($this->createMock(User::class));
        $this->assertFalse($this->validator->isValid($command));
    }
}
