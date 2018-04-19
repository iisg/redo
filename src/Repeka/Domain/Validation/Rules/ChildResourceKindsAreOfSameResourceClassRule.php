<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Utils\EntityUtils;
use Respect\Validation\Rules\AbstractRule;

class ChildResourceKindsAreOfSameResourceClassRule extends AbstractRule {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /**
     * @param Metadata[] $input
     * @return bool
     */
    public function validate($input): bool {
        $parentMetadata = current(EntityUtils::filterByIds([SystemMetadata::PARENT], $input));
        $anyOtherMetadata = current(
            array_filter(
                $input,
                function (Metadata $metadata) {
                    return $metadata->getResourceClass() !== "";
                }
            )
        );
        if (!$anyOtherMetadata || !$parentMetadata) {
            return false;
        }
        $resourceClass = $anyOtherMetadata->getResourceClass();
        $allowedChildRkIds = $parentMetadata->getConstraints()['resourceKind'] ?? [];
        if ($allowedChildRkIds) {
            foreach ($allowedChildRkIds as $resourceKindId) {
                $resourceKind = $this->resourceKindRepository->findOne($resourceKindId);
                if ($resourceKind->getResourceClass() !== $resourceClass) {
                    return false;
                }
            }
        }
        return true;
    }
}
