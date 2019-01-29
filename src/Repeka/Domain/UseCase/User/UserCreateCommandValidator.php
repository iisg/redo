<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserCreateCommandValidator extends CommandAttributesValidator {
    /** @var UserLoaderInterface */
    private $userLoader;
    /** @var ResourceContentsCorrectStructureRule */
    private $resourceContentsCorrectStructureRule;

    public function __construct(
        UserLoaderInterface $userLoader,
        ResourceContentsCorrectStructureRule $resourceContentsCorrectStructureRule
    ) {
        $this->userLoader = $userLoader;
        $this->resourceContentsCorrectStructureRule = $resourceContentsCorrectStructureRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute(
            'username',
            Validator::notBlank()->callback(
                function ($username) {
                    return $this->userLoader->loadUserByUsername($username) == null;
                }
            )->setTemplate('User already exists')
        )
            ->attribute('userData', $this->resourceContentsCorrectStructureRule);
    }
}
