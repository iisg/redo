<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ResourceWorkflowViewVoter extends ResourceKindViewVoter {

    /** @var CommandBus */
    private $commandBus;

    public function __construct(
        CommandBus $commandBus,
        ReproductorPermissionHelper $reproductorPermissionHelper,
        UnauthenticatedUserPermissionHelper $unauthenticatedUserPermissionHelper
    ) {
        parent::__construct($reproductorPermissionHelper, $unauthenticatedUserPermissionHelper);
        $this->commandBus = $commandBus;
    }

    protected function supports($attribute, $subject) {
        return $attribute === 'VIEW' && $subject instanceof ResourceWorkflow;
    }

    /**
     * @inheritdoc
     * @param ResourceWorkflow $workflow
     */
    public function voteOnAttribute($attribute, $workflow, TokenInterface $token) {
        $query = ResourceKindListQuery::builder()->filterByWorkflowId($workflow->getId())->build();
        $resourceKinds = $this->commandBus->handle($query);
        foreach ($resourceKinds as $resourceKind) {
            if (parent::voteOnAttribute($attribute, $resourceKind, $token)) {
                return true;
            }
        }
        return false;
    }
}
