<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MetadataViewVoter extends ResourceKindViewVoter {

    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus, ReproductorPermissionHelper $reproductorPermissionHelper) {
        parent::__construct($reproductorPermissionHelper);
        $this->commandBus = $commandBus;
    }

    protected function supports($attribute, $subject) {
        return $attribute === 'VIEW' && $subject instanceof Metadata;
    }

    /**
     * @inheritdoc
     * @param Metadata $metadata
     */
    public function voteOnAttribute($attribute, $metadata, TokenInterface $token): bool {
        $query = ResourceKindListQuery::builder()->filterByMetadataId($metadata->getTopParent()->getId())->build();
        $resourceKinds = $this->commandBus->handle($query);
        foreach ($resourceKinds as $resourceKind) {
            if (parent::voteOnAttribute($attribute, $resourceKind, $token)) {
                return true;
            }
        }
        return false;
    }
}
