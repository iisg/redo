<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\RepositoryProvider;
use Respect\Validation\Rules\AbstractRule;

class EntityExistsRule extends AbstractRule {
    /** @var RepositoryProvider */
    private $repositoryProvider;
    private $entityRepository;

    public function __construct(RepositoryProvider $repositoryProvider) {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function forEntityType(string $typeClass): EntityExistsRule {
        $instance = new self($this->repositoryProvider);
        $instance->entityRepository = $this->repositoryProvider->getForEntityType($typeClass);
        return $instance;
    }

    public function validate($input) {
        Assertion::notNull(
            $this->entityRepository,
            'Repository not set. Use forEntityType() to create validator for specific entity first.'
        );
        try {
            $this->entityRepository->findOne($input);
            return true;
        } catch (EntityNotFoundException $exception) {
            return false;
        }
    }
}
