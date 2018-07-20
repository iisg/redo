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
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->fetchMetadataIfRequired($command->getMetadataList()),
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
        if (!isset($metadataList[SystemMetadata::PARENT])) {
            $metadataList[SystemMetadata::PARENT] = $this->metadataRepository->findOne(SystemMetadata::PARENT);
        }
        if (!isset($metadataList[SystemMetadata::REPRODUCTOR])) {
            $metadataList[SystemMetadata::REPRODUCTOR] = $this->metadataRepository->findOne(SystemMetadata::REPRODUCTOR);
        }
        if (!isset($metadataList[SystemMetadata::RESOURCE_LABEL])) {
            $metadataList[SystemMetadata::RESOURCE_LABEL] = $this->metadataRepository->findOne(SystemMetadata::RESOURCE_LABEL);
        }
        return array_values($metadataList);
    }

    private function clearUnsupportedConstraints(Metadata $metadata, array $constraints): array {
        $controlKeys = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($metadata->getControl());
        $controlKeys = array_combine($controlKeys, $controlKeys);
        $allowedConstraints = array_intersect_key($constraints, $controlKeys);
        return $allowedConstraints;
    }

    protected function findWorkflow($workflowOrId) {
        if (!$workflowOrId || $workflowOrId instanceof ResourceWorkflow) {
            return $workflowOrId;
        }
        return $this->workflowRepository->findOne($workflowOrId);
    }
}
