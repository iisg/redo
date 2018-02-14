<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
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
        $resourceChildrenQueryBuilder = ResourceListQuery::builder()->filterByParentId($input->getId())->build();
        $pageResult = $this->resourceRepository->findByQuery($resourceChildrenQueryBuilder);
        return empty($pageResult->getResults());
    }
}
