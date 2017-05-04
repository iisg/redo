<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validator;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserCreateCommandValidator extends CommandAttributesValidator {
    /** @var UserLoaderInterface */
    private $userLoader;

    public function __construct(UserLoaderInterface $userLoader) {
        $this->userLoader = $userLoader;
    }

    public function getValidator(Command $command): Validator {
        return Validator::attribute('username', Validator::notBlank()->callback(function ($username) {
            return ($this->userLoader->loadUserByUsername($username) == null);
        }));
    }
}
