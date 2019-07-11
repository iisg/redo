<?php
namespace Repeka\Domain\Workflow\Loader;

use Assert\Assertion;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandHandler;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Utils\StringUtils;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 */
class ResourcesSchemaLoader {
    use Transactional;

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var CommandBus */
    private $commandBus;
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceKindRepository $resourceKindRepository,
        ResourceContentsAdjuster $resourceContentsAdjuster,
        ResourceRepository $resourceRepository,
        CommandBus $commandBus
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
        $this->commandBus = $commandBus;
        $this->resourceRepository = $resourceRepository;
    }

    public function load(array $schemaConfiguration) {
        FirewallMiddleware::bypass(
            function () use ($schemaConfiguration) {
                $this->transactional(
                    function () use ($schemaConfiguration) {
                        $this->loadSchema($schemaConfiguration);
                    }
                );
            }
        );
    }

    private function loadSchema(array $schema) {
        $systemMetadata = $this->getObligatorySystemMetadata();
        foreach ($schema['resourceKinds'] as $schemaConfiguration) {
            Assertion::keyExists($schemaConfiguration, 'resourceClass');
            Assertion::keyExists($schemaConfiguration, 'metadata');
            Assertion::keyExists($schemaConfiguration, 'resourceKind');
            $resourceClass = $schemaConfiguration['resourceClass'];
            $metadataList = $this->loadTopLevelMetadata($resourceClass, $systemMetadata, $schemaConfiguration['metadata']);
            $workflow = isset($schemaConfiguration['workflow'])
                ? $this->loadWorkflow($resourceClass, $schemaConfiguration['workflow'], $metadataList)
                : null;
            $this->createOrUpdateResourceKind($schemaConfiguration['resourceKind'], $metadataList, $workflow);
        }
        $this->addConstraintsToUserRelatedMetadata($systemMetadata);
        if (isset($schema['resources'])) {
            $this->loadResources($schema['resources']);
        }
    }

    private function loadWorkflow(string $resourceClass, array $workflowSchema, array $metadataList): ResourceWorkflow {
        $places = $transitions = [];
        foreach ($workflowSchema['places'] as $placeDefinition) {
            $placeConfig = array_merge($this->buildPlaceMetadataRequirements($placeDefinition, $metadataList), $placeDefinition);
            $places[] = ResourceWorkflowPlace::fromArray($placeConfig);
        }
        foreach ($workflowSchema['transitions'] as $transitionDefinition) {
            $transitions[] = ResourceWorkflowTransition::fromArray($transitionDefinition);
        }
        return $this->createOrUpdateWorkflow($resourceClass, $workflowSchema['label'], $places, $transitions);
    }

    private function loadTopLevelMetadata(string $resourceClass, array $systemMetadataList, array $metadataSchema): array {
        $metadataList = [];
        foreach ($metadataSchema as $metadataConfig) {
            Assertion::keyExists($metadataConfig, 'name');
            $metadataList[StringUtils::normalizeEntityName($metadataConfig['name'])] =
                $this->createOrUpdateMetadata($resourceClass, $metadataConfig);
        }
        foreach ($systemMetadataList as $systemMetadata) {
            if (!isset($metadataList[$systemMetadata->getName()])) {
                $metadataList[$systemMetadata->getName()] = $systemMetadata;
            }
        }
        return $metadataList;
    }

    private function getObligatorySystemMetadata(): array {
        return $this->metadataRepository->findByQuery(
            MetadataListQuery::builder()->filterByIds(
                [
                    SystemMetadata::TEASER_VISIBILITY,
                    SystemMetadata::VISIBILITY,
                    SystemMetadata::REPRODUCTOR,
                ]
            )->build()
        );
    }

    private function addConstraintsToUserRelatedMetadata($userRelatedMetadata) {
        $groupResourceKind = $this->resourceKindRepository->findByName('grupa');
        $groupMetadata = $this->metadataRepository->findByName('group_member');
        $this->addSupportForResourceKindToMetadata($groupMetadata, [$groupResourceKind->getId()]);
        foreach ($userRelatedMetadata as $metadata) {
            $this->addSupportForResourceKindToMetadata($metadata, [SystemResourceKind::USER, $groupResourceKind->getId()]);
        }
    }

    private function addSupportForResourceKindToMetadata(Metadata $metadata, array $resourceKindIds) {
        $constraints = $metadata->getConstraints();
        $supportedResourceKinds = array_merge($constraints['resourceKind'] ?? [], $resourceKindIds);
        $constraints['resourceKind'] = $supportedResourceKinds;
        $query = new MetadataUpdateCommand(
            $metadata,
            $metadata->getLabel(),
            $metadata->getDescription(),
            $metadata->getPlaceholder(),
            $constraints,
            $metadata->getGroupId(),
            $metadata->getDisplayStrategy(),
            $metadata->isShownInBrief(),
            $metadata->isCopiedToChildResource()
        );
        $this->commandBus->handle($query);
    }

    private function loadResources(array $resourceScheme) {
        $references = [];
        $relationshipMetadataQuery = MetadataListQuery::builder()->filterByControl(MetadataControl::RELATIONSHIP())->build();
        $relationshipMetadata = $this->metadataRepository->findByQuery($relationshipMetadataQuery);
        foreach ($resourceScheme as $resourceConfig) {
            $resourceKind = $this->resourceKindRepository->findByName($resourceConfig['resourceKind']);
            $identifiableMetadata = $this->metadataRepository->findByName($resourceConfig['identifiedBy']);
            $commonMetadata = $resourceConfig['commonMetadata'] ?? [];
            $ref = $resourceConfig['ref'] ?? null;
            $refIndex = 0;
            foreach ($resourceConfig['instances'] as $resourceContents) {
                $resourceContents = array_merge($commonMetadata, $resourceContents);
                foreach ($relationshipMetadata as $referencableMetadata) {
                    $value = $resourceContents[$referencableMetadata->getName()] ?? null;
                    if ($value && !is_array($value)) {
                        $value = [$value];
                    }
                    if ($value) {
                        $values = [];
                        foreach ($value as $valueRef) {
                            if ($valueRef && isset($references[$valueRef])) {
                                $values[] = $references[$valueRef];
                            } else {
                                $values[] = $valueRef;
                            }
                        }
                        $resourceContents[$referencableMetadata->getName()] = $values;
                    }
                }
                $resource = $this->createOrUpdateResource($resourceKind, $identifiableMetadata, $resourceContents);
                if ($ref) {
                    $references['REF:' . $ref . '/' . $refIndex++] = $resource;
                }
            }
        }
        $this->updateReferences($references);
    }

    /**
     * @SuppressWarnings("PHPMD.NPathComplexity")
     */
    private function createOrUpdateMetadata(string $resourceClass, array $metadataConfig): Metadata {
        if (!isset($metadataConfig['groupId'])) {
            $metadataConfig['groupId'] = 'basic';
        }
        if (!isset($metadataConfig['constraints']['maxCount'])) {
            $metadataConfig = array_merge_recursive(['constraints' => ['maxCount' => 1]], $metadataConfig);
        }
        if (isset($metadataConfig['constraints']['resourceKind'])) {
            $metadataConfig['constraints']['resourceKind'] = array_map(
                function ($rkIdOrName) {
                    if (!is_numeric($rkIdOrName)) {
                        $rkIdOrName = $this->resourceKindRepository->findByName($rkIdOrName)->getId();
                    }
                    return $rkIdOrName;
                },
                $metadataConfig['constraints']['resourceKind']
            );
        }
        if (($metadataConfig['control'] ?? null) == MetadataControl::FILE && !isset($metadataConfig['description'])) {
            $description = "Maksymalny rozmiar pliku to " . ini_get("upload_max_filesize");
            if (isset($metadataConfig['constraints']['allowedFileExtensions'])) {
                $allowedExtensions = implode(', ', $metadataConfig['constraints']['allowedFileExtensions']);
                $description .= ", a dozwolone rozszerzenia - $allowedExtensions";
            }
            $metadataConfig['description'] = ['PL' => $description];
        }
        $metadataToReturn = null;
        try {
            $metadataToReturn = $this->metadataRepository->findByName($metadataConfig['name']);
            if (SystemMetadata::isValid($metadataToReturn->getId())) {
                return $metadataToReturn->withOverrides($metadataConfig);
            } elseif (isset($metadataConfig['label'])) {
                $updateHandler = new MetadataUpdateCommandHandler($this->metadataRepository);
                $metadataToReturn = $updateHandler->handle(MetadataUpdateCommand::fromArray($metadataToReturn, $metadataConfig));
            }
        } catch (EntityNotFoundException $exception) {
            $metadataConfig['resourceClass'] = $resourceClass;
            $command = MetadataCreateCommand::fromArray($metadataConfig);
            $factory = new MetadataFactory();
            $metadata = $factory->create($command);
            $this->metadataRepository->save($metadata);
            $metadataToReturn = $metadata;
        }
        foreach (($metadataConfig['submetadata'] ?? []) as $submetadataConfig) {
            $submetadata = $this->createOrUpdateMetadata($resourceClass, $submetadataConfig);
            $submetadata->setParent($metadataToReturn);
            $this->metadataRepository->save($submetadata);
        }
        return $metadataToReturn;
    }

    private function createOrUpdateWorkflow(string $resourceClass, array $label, array $places, array $transitions): ResourceWorkflow {
        /** @var ResourceWorkflow[] $workflows */
        $workflows = $this->commandBus->handle(new ResourceWorkflowListQuery($resourceClass));
        foreach ($workflows as $w) {
            if ($w->getName()['PL'] == $label['PL']) {
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

    private function createOrUpdateResourceKind(array $config, array $metadataList, ?ResourceWorkflow $workflow): ResourceKind {
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
            $command = new ResourceKindUpdateCommand($resourceKind, $config['label'], $metadataList, false, $workflow);
        } catch (EntityNotFoundException $e) {
            $command = new ResourceKindCreateCommand($config['name'], $config['label'], $metadataList, false, $workflow);
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
        ];
        $requirements['lockedMetadataIds'] = EntityUtils::mapToIds($metadataList);
        return $requirements;
    }

    private function createOrUpdateResource(ResourceKind $resourceKind, Metadata $identifiableMetadata, array $resourceContents) {
        $newContents = $this->resourceContentsAdjuster->adjust($resourceContents);
        $identifiableValue = current($newContents->getValuesWithoutSubmetadata($identifiableMetadata));
        $identifiableValue = addcslashes($identifiableValue, '()[].*+');
        $query = ResourceListQuery::builder()
            ->filterByResourceKind($resourceKind)
            ->filterByContents([$identifiableMetadata->getId() => "^" . $identifiableValue . "$"])
            ->build();
        $resources = $this->resourceRepository->findByQuery($query);
        if ($resources->count()) {
            $resource = $resources->getResults()[0];
        } else {
            $resource = new ResourceEntity($resourceKind, ResourceContents::empty());
            $resource = $this->resourceRepository->save($resource);
        }
        $newContents = $this->ensureExistingContentsNotRemoved($resource->getContents(), $newContents);
        if ($newContents != $resource->getContents()) {
            $update = ResourceGodUpdateCommand::builder()->setResource($resource)->setNewContents($newContents)->build();
            $this->commandBus->handle($update);
            $this->commandBus->handle(new ResourceEvaluateDisplayStrategiesCommand($resource));
        }
        return $resource;
    }

    private function ensureExistingContentsNotRemoved(ResourceContents $oldContents, ResourceContents $newContents) {
        foreach ($newContents->toArray() as $metadata => $values) {
            $oldContents = $oldContents->withReplacedValues($metadata, $values);
        }
        return $oldContents;
    }

    private function updateReferences(array $references) {
        $queries = [];
        foreach ($references as $refName => $refResource) {
            $queries[] =
                "UPDATE metadata SET constraints = constraints || '{\"relatedResourceMetadataFilter\": 
                {\"-1\": {$refResource->getId()}}}'::jsonb WHERE constraints #>> '{relatedResourceMetadataFilter, -1}' = '$refName';";
        }
        foreach ($queries as $query) {
            $this->entityManager->getConnection()->executeQuery($query);
        }
    }
}
