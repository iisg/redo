<?php
namespace Repeka\Tests\Traits;

use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\FluentRestrictingMetadataSelector;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Respect\Validation\Exceptions\ValidationException;

/**
 * @method \PHPUnit_Framework_MockObject_MockObject createMock(string $originalClassName)
 */
trait StubsTrait {
    /** @return LanguageRepository */
    protected function createLanguageRepositoryMock(array $languages): \PHPUnit_Framework_MockObject_MockObject {
        $mock = $this->createMock(LanguageRepository::class);
        $mock->method('getAvailableLanguageCodes')->willReturn($languages);
        return $mock;
    }

    protected function createMockEntity(string $className, int $id): \PHPUnit_Framework_MockObject_MockObject {
        $metadata = $this->createMock($className);
        $metadata->method('getId')->willReturn($id);
        return $metadata;
    }

    /** @return Metadata|\PHPUnit_Framework_MockObject_MockObject */
    protected function createMetadataMock(
        int $id = 1,
        ?int $baseId = null,
        MetadataControl $control = null,
        array $constraints = [],
        string $resourceClass = 'books',
        array $overrides = [],
        string $name = ''
    ): Metadata {
        if ($control == null) {
            $control = MetadataControl::TEXTAREA();
        }
        /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->createMockEntity(Metadata::class, $id);
        $metadata->method('getBaseId')->willReturn($baseId);
        $metadata->method('isBase')->willReturn($baseId === null);
        $metadata->method('getControl')->willReturn($control);
        $metadata->method('getConstraints')->willReturn($constraints);
        $metadata->method('getResourceClass')->willReturn($resourceClass);
        $metadata->method('withOverrides')->willReturn($metadata);
        $metadata->method('getOverrides')->willReturn($overrides);
        $metadata->method('getName')->willReturn($name ?: 'metadata' . $id);
        return $metadata;
    }

    /**
     * @param int $id
     * @param string $resourceClass
     * @param Metadata[] $metadataList
     * @param ResourceWorkflow|null $workflow
     * @return ResourceKind|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createResourceKindMock(
        int $id = 1,
        $resourceClass = 'books',
        array $metadataList = [],
        ResourceWorkflow $workflow = null
    ): ResourceKind {
        $resourceKind = $this->createMock(ResourceKind::class);
        $resourceKind->method('getId')->willReturn($id);
        $resourceKind->method('getResourceClass')->willReturn($resourceClass);
        $resourceKind->method('getMetadataList')->willReturn($metadataList);
        $resourceKind->method('getWorkflow')->willReturn($workflow);
        $resourceKind->method('getMetadataIds')->willReturn(EntityUtils::mapToIds($metadataList));
        $resourceKind->method('getMetadataByIdOrName')->willReturnCallback(
            function ($id) use ($metadataList) {
                foreach ($metadataList as $metadata) {
                    if ($metadata->getId() == $id) {
                        return $metadata;
                    }
                }
                throw new \InvalidArgumentException();
            }
        );
        return $resourceKind;
    }

    /** @return ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    protected function createResourceMock(int $id, ?ResourceKind $resourceKind = null, $contents = [], $marking = []): ResourceEntity {
        $mock = $this->createMock(ResourceEntity::class);
        if (!$resourceKind) {
            $resourceKind = $this->createResourceKindMock();
        }
        $mock->method('getKind')->willReturn($resourceKind);
        $mock->method('getResourceClass')->willReturn($resourceKind->getResourceClass());
        $mock->method('getId')->willReturn($id);
        $contents = $contents instanceof ResourceContents ? $contents : ResourceContents::fromArray($contents);
        $mock->method('getContents')->willReturn($contents);
        $mock->method('getMarking')->willReturn($marking);
        $mock->method('hasWorkflow')->willReturn($resourceKind && $resourceKind->getWorkflow());
        $mock->method('getWorkflow')->willReturn($resourceKind ? $resourceKind->getWorkflow() : null);
        return $mock;
    }

    protected function createEntityLookupMap(array $entityList): array {
        $result = [];
        foreach ($entityList as $metadata) {
            $result[$metadata->getId()] = $metadata;
        }
        return $result;
    }

    /**
     * @param Metadata[] $metadataList
     * @return MetadataRepository
     */
    protected function createRepositoryStub(string $repositoryClassName, array $entityList = []): \PHPUnit_Framework_MockObject_MockObject {
        $lookup = key($entityList) === 0 ? EntityUtils::getLookupMap($entityList) : $entityList;
        $idCounter = 1;
        $repository = $this->createMock($repositoryClassName);
        $repository->method('findAll')->willReturn($entityList);
        $repository->method('findOne')->willReturnCallback(
            function ($id) use ($lookup, $repositoryClassName) {
                if (array_key_exists($id, $lookup)) {
                    return $lookup[$id];
                } else {
                    throw new EntityNotFoundException($repositoryClassName . 'Mock', $id);
                }
            }
        );
        $repository->method('save')->willReturnCallback(
            function ($entity) use (&$idCounter) {
                /** @var Identifiable $entity */
                if ($entity->getId() === null) {
                    // Entities returned by save() method must have an ID assigned.
                    EntityUtils::forceSetId($entity, $idCounter++);
                }
                return $entity;
            }
        );
        return $repository;
    }

    /**
     * @param Metadata[] $metadataList
     * @return MetadataRepository
     */
    protected function createMetadataRepositoryStub(array $metadataList = []): \PHPUnit_Framework_MockObject_MockObject {
        $repository = $this->createRepositoryStub(MetadataRepository::class, $metadataList);
        $repository->method('findByName')->willReturnCallback(
            function (string $name) use ($metadataList) {
                foreach ($metadataList as $metadata) {
                    if ($metadata->getName() === $name) {
                        return $metadata;
                    }
                }
                throw new EntityNotFoundException('Metadata', $name);
            }
        );
        return $repository;
    }

    /** @return MetadataConstraintManager */
    protected function createMetadataConstraintManagerStub(array $namesToConstraintsMap): \PHPUnit_Framework_MockObject_MockObject {
        $stub = $this->createMock(MetadataConstraintManager::class);
        $stub->method('get')->willReturnCallback(
            function ($ruleName) use ($namesToConstraintsMap) {
                if (array_key_exists($ruleName, $namesToConstraintsMap)) {
                    return $namesToConstraintsMap[$ruleName];
                } else {
                    throw new \InvalidArgumentException("MetadataConstraintManager stub doesn't contain validator for '$ruleName'");
                }
            }
        );
        return $stub;
    }

    /**
     * Make sure mocked rule doesn't have factory methods, such as forResourceKind(). These methods will return broken mocks by default,
     * which can produce false positives. If rule has factory methods, use createRuleWithFactoryMethodMock();
     */
    protected function createRuleMock(string $ruleClass, bool $result, ?string $exceptionMessage = null) {
        $mock = $this->createMock($ruleClass);
        $mock->method('validate')->willReturn($result);
        $mock->method('assert')->willReturnCallback(
            function () use ($result, $exceptionMessage) {
                if ($result) {
                    return true;
                } else {
                    throw new ValidationException($exceptionMessage);
                }
            }
        );
        return $mock;
    }

    /** @param string|string[] $methodName */
    protected function createRuleWithFactoryMethodMock(
        string $ruleClass,
        $methodName,
        ?bool $result = null,
        ?string $exceptionMessage = null
    ) {
        $methodNames = is_array($methodName) ? $methodName : [$methodName];
        $mock = is_null($result)
            ? $this->createMock($ruleClass)
            : $this->createRuleMock($ruleClass, $result, $exceptionMessage);
        foreach ($methodNames as $methodName) {
            $mock->method($methodName)->willReturnSelf();
        }
        return $mock;
    }

    protected function createEntityExistsMock(bool $result, ?string $exceptionMessage = null): EntityExistsRule {
        $mock = $this->createRuleWithFactoryMethodMock(EntityExistsRule::class, 'forEntityType', $result, $exceptionMessage);
        $mock->method('forEntityType')->willReturnSelf();
        return $mock;
    }

    /** @return ResourceWorkflowPlace|\PHPUnit_Framework_MockObject_MockObject */
    protected function createWorkflowPlaceMock(
        string $id = '',
        array $missingMetadataIds = [],
        array $assigneeMetadataIds = [],
        array $autoAssignMetadataIds = [],
        array $label = []
    ): ResourceWorkflowPlace {
        $mock = $this->createMock(ResourceWorkflowPlace::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('getMissingRequiredMetadataIds')->willReturn($missingMetadataIds);
        $mock->method('resourceHasRequiredMetadata')->willReturn(empty($missingMetadataIds));
        $mock->method('restrictingMetadataIds')->willReturnCallback(
            function () use ($autoAssignMetadataIds, $assigneeMetadataIds) {
                return new FluentRestrictingMetadataSelector([], [], $assigneeMetadataIds, $autoAssignMetadataIds);
            }
        );
        $mock->method('getLabel')->willReturn($label);
        return $mock;
    }

    /** @return ResourceWorkflowTransition|\PHPUnit_Framework_MockObject_MockObject */
    protected function createWorkflowTransitionMock(
        array $label = [],
        array $fromIds = [],
        array $toIds = [],
        $id = null,
        array $permittedRoleIds = []
    ): ResourceWorkflowTransition {
        $mock = $this->createMock(ResourceWorkflowTransition::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('getLabel')->willReturn($label);
        $mock->method('getFromIds')->willReturn($fromIds);
        $mock->method('getToIds')->willReturn($toIds);
        $mock->method('getPermittedRoleIds')->willReturn($permittedRoleIds);
        return $mock;
    }
}
