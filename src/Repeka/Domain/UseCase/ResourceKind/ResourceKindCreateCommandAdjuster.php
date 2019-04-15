<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceKindCreateCommandAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var UnknownLanguageStripper */
    protected $unknownLanguageStripper;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    public function __construct(
        MetadataRepository $metadataRepository,
        UnknownLanguageStripper $unknownLanguageStripper,
        MetadataConstraintManager $metadataConstraintManager,
        ResourceWorkflowRepository $workflowRepository
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->unknownLanguageStripper = $unknownLanguageStripper;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @return ResourceKindCreateCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceKindCreateCommand(
            StringUtils::normalizeEntityName($command->getName()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->fetchMetadataIfRequired($command->getMetadataList()),
            $command->isAllowedToClone(),
            $this->findWorkflow($command->getWorkflow())
        );
    }

    protected function fetchMetadataIfRequired($metadataOrOverrideList): array {
        /** @var Metadata[] $metadataList */
        $metadataList = [];
        foreach ($metadataOrOverrideList as $metadataOrOverride) {
            if ($metadataOrOverride instanceof Metadata) {
                $metadataList[$metadataOrOverride->getId()] = $metadataOrOverride;
            } else {
                Assertion::isArray($metadataOrOverride, 'Invalid metadata override format.');
                Assertion::keyExists($metadataOrOverride, 'id', 'Metadata id is requried when using override format.');
                $id = (int)$metadataOrOverride['id'];
                $metadata = $this->metadataRepository->findOne($id);
                if (isset($metadataOrOverride['constraints'])) {
                    $metadataOrOverride['constraints'] = $this->clearUnsupportedConstraints($metadata, $metadataOrOverride['constraints']);
                }
                foreach (['label', 'placeholder', 'description'] as $multilingualAttribute) {
                    if (isset($metadataOrOverride[$multilingualAttribute])) {
                        $metadataOrOverride[$multilingualAttribute] = $this->unknownLanguageStripper->removeUnknownLanguages(
                            $metadataOrOverride[$multilingualAttribute]
                        );
                    }
                }
                $metadataList[$id] = $metadata->withOverrides($metadataOrOverride);
            }
        }
        $this->addSystemMetadataIfMissing($metadataList);
        return array_values($metadataList);
    }

    private function clearUnsupportedConstraints(Metadata $metadata, array $constraints): array {
        $controlKeys = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($metadata->getControl());
        $controlKeys = array_combine($controlKeys, $controlKeys);
        $allowedConstraints = array_intersect_key($constraints, $controlKeys);
        return $allowedConstraints;
    }

    private function addSystemMetadataIfMissing(&$metadataList) {
        $userRelatedSystemMetadata = [SystemMetadata::USERNAME, SystemMetadata::GROUP_MEMBER];
        $obligatorySystemMetadata = array_diff(SystemMetadata::toArray(), $userRelatedSystemMetadata);
        foreach ($obligatorySystemMetadata as $systemMetadata) {
            if (!isset($metadataList[$systemMetadata]) || $systemMetadata == SystemMetadata::REPRODUCTOR) {
                $metadataList[$systemMetadata] = $this->metadataRepository->findOne($systemMetadata);
            }
        }
    }

    protected function findWorkflow($workflowOrId) {
        if (!$workflowOrId || $workflowOrId instanceof ResourceWorkflow) {
            return $workflowOrId;
        }
        return $this->workflowRepository->findOne($workflowOrId);
    }
}
