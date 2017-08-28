<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Respect\Validation\Rules\AbstractRule;

class NoResourcesOfKindExistRule extends AbstractRule {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /** @param ResourceKind $input */
    public function validate($input) {
        return $this->resourceRepository->countByResourceKind($input) == 0;
    }
}
