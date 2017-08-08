<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Respect\Validation\Rules\AbstractRule;

class ResourceHasNoChildrenRule extends AbstractRule {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function validate($input): bool {
        Assertion::isInstanceOf($input, ResourceEntity::class);
        /** @var ResourceEntity $input */
        $children = $this->resourceRepository->findChildren($input->getId());
        return empty($children);
    }
}
