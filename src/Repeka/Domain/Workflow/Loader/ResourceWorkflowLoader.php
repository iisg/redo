<?php
namespace Repeka\Domain\Workflow\Loader;

use Assert\Assertion;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Utils\StringUtils;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceWorkflowLoader {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceKindRepository $resourceKindRepository,
        CommandBus $commandBus
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->commandBus = $commandBus;
    }

    public function load(array $workflowConfiguration) {
        FirewallMiddleware::bypass(
            function () use ($workflowConfiguration) {
                $this->loadWorkflow($workflowConfiguration);
            }
        );
    }

    private function loadWorkflow(array $workflowConfiguration) {
        Assertion::keyExists($workflowConfiguration, 'label');
        Assertion::keyExists($workflowConfiguration, 'resourceClass');
        Assertion::keyExists($workflowConfiguration, 'places');
        Assertion::keyExists($workflowConfiguration, 'transitions');
        Assertion::keyExists($workflowConfiguration, 'metadata');
        Assertion::keyExists($workflowConfiguration, 'resourceKind');
        $resourceClass = $workflowConfiguration['resourceClass'];
        $metadataList = $places = $transitions = [];
        foreach ($workflowConfiguration['metadata'] as $metadataConfig) {
            Assertion::keyExists($metadataConfig, 'name');
            $metadataList[StringUtils::normalizeEntityName($metadataConfig['name'])] =
                $this->createOrUpdateMetadata($resourceClass, $metadataConfig);
        }
        $query = MetadataListQuery::builder()
            ->filterByResourceClass($resourceClass)
            ->addSystemMetadataIds([SystemMetadata::REPRODUCTOR, SystemMetadata::TEASER_VISIBILITY, SystemMetadata::VISIBILITY])
            ->build();
        $allMetadata = $this->metadataRepository->findByQuery($query);
        foreach ($allMetadata as $existingMetadata) {
            if (!isset($metadataList[$existingMetadata->getName()])) {
                $metadataList[$existingMetadata->getName()] = $existingMetadata;
            }
        }
        foreach ($workflowConfiguration['places'] as $placeDefinition) {
            $placeConfig = array_merge($this->buildPlaceMetadataRequirements($placeDefinition, $metadataList), $placeDefinition);
            $places[] = ResourceWorkflowPlace::fromArray($placeConfig);
        }
        foreach ($workflowConfiguration['transitions'] as $transitionDefinition) {
            $transitions[] = ResourceWorkflowTransition::fromArray($transitionDefinition);
        }
        $workflow = $this->createOrUpdateWorkflow($resourceClass, $workflowConfiguration['label'], $places, $transitions);
        $this->createOrUpdateResourceKind($workflowConfiguration['resourceKind'], $metadataList, $workflow);
    }

    private function createOrUpdateMetadata(string $resourceClass, array $metadataConfig): Metadata {
        try {
            $metadata = $this->metadataRepository->findByName($metadataConfig['name']);
            if (SystemMetadata::isValid($metadata->getId())) {
                return $metadata->withOverrides($metadataConfig);
            } else {
                return $this->commandBus->handle(MetadataUpdateCommand::fromArray($metadata, $metadataConfig));
            }
        } catch (EntityNotFoundException $exception) {
            $metadataConfig['resourceClass'] = $resourceClass;
            return $this->commandBus->handle(MetadataCreateCommand::fromArray($metadataConfig));
        }
    }

    private function createOrUpdateWorkflow(string $resourceClass, array $label, array $places, array $transitions): ResourceWorkflow {
        /** @var ResourceWorkflow[] $workflows */
        $workflows = $this->commandBus->handle(new ResourceWorkflowListQuery($resourceClass));
        foreach ($workflows as $w) {
            if ($w->getName() == $label) {
                $workflow = $w;
                break;
            }
        }
        if (isset($workflow)) {
            return $this->commandBus->handle(
                new ResourceWorkflowUpdateCommand(
                    $workflow,
                    $label,
                    $places,
                    $transitions,
                    $workflow->getDiagram(),
                    $workflow->getThumbnail()
                )
            );
        } else {
            return $this->commandBus->handle(
                new ResourceWorkflowCreateCommand($label, $places, $transitions, $resourceClass, null, null)
            );
        }
    }

    private function createOrUpdateResourceKind(array $config, array $metadataList, ResourceWorkflow $workflow): ResourceKind {
        try {
            $resourceKind = $this->resourceKindRepository->findByName($config['name']);
            $currentMetadataList = $resourceKind->getMetadataList();
            $currentMetadataList = array_values(
                array_filter(
                    $currentMetadataList,
                    function (Metadata $metadata) use ($metadataList) {
                        return !isset($metadataList[$metadata->getName()]);
                    }
                )
            );
            $metadataList = array_merge(array_values($metadataList), $currentMetadataList);
            $command = new ResourceKindUpdateCommand($resourceKind, $config['label'], $metadataList, $workflow);
        } catch (EntityNotFoundException $e) {
            $command = new ResourceKindCreateCommand($config['name'], $config['label'], $metadataList, $workflow);
        }
        return $this->commandBus->handle($command);
    }

    private function buildPlaceMetadataRequirements(array $placeDefinition, array $metadataList): array {
        if (isset($metadataList['label'])) {
            unset($metadataList['label']);
        }
        $mapByName = function (string $name) use (&$metadataList) {
            $key = StringUtils::normalizeEntityName($name);
            Assertion::keyExists($metadataList, $key);
            $metadata = $metadataList[$key];
            unset($metadataList[$key]);
            return $metadata;
        };
        array_map($mapByName, $placeDefinition['optionalMetadata'] ?? []);
        $requirements = [
            'requiredMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $placeDefinition['requiredMetadata'] ?? [])),
            'assigneeMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $placeDefinition['assigneeMetadata'] ?? [])),
            'autoAssignMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $placeDefinition['autoAssignMetadata'] ?? [])),
            'lockedMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $placeDefinition['lockedMetadata'] ?? [])),
        ];
        if ($metadataList) {
            $requirements['lockedMetadataIds'] = array_merge($requirements['lockedMetadataIds'], EntityUtils::mapToIds($metadataList));
        }
        return $requirements;
    }
}
