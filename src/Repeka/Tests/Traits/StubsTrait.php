<?php
namespace Repeka\Tests\Traits;

use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\FluentRestrictingMetadataSelector;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\MetadataRepository;
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
        string $resourceClass = 'books'
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
        return $resourceKind;
    }

    /** @return ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    protected function createResourceMock(int $id, ?ResourceKind $resourceKind = null, array $contents = []): ResourceEntity {
        $mock = $this->createMock(ResourceEntity::class);
        $mock->method('getKind')->willReturn($resourceKind);
        $mock->method('getId')->willReturn($id);
        $mock->method('getContents')->willReturn($contents);
        if ($contents) {
            $mock->method('getValues')->willReturnCallback(function ($metadata) use ($contents) {
                /** @var Metadata $metadata */
                if (array_key_exists($metadata->getId(), $contents)) {
                    return $contents[$metadata->getId()];
                } else {
                    return [];
                }
            });
        }
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
        $repository->method('findOne')->willReturnCallback(function ($id) use ($lookup, $repositoryClassName) {
            if (array_key_exists($id, $lookup)) {
                return $lookup[$id];
            } else {
                throw new EntityNotFoundException($repositoryClassName . 'Mock', $id);
            }
        });
        $repository->method('save')->willReturnCallback(function ($entity) use (&$idCounter) {
            /** @var Identifiable $entity */
            if ($entity->getId() === null) {
                // Entities returned by save() method must have an ID assigned.
                EntityUtils::forceSetId($entity, $idCounter++);
            }
            return $entity;
        });
        return $repository;
    }

    /**
     * @param Metadata[] $metadataList
     * @return MetadataRepository
     */
    protected function createMetadataRepositoryStub(array $metadataList = []): \PHPUnit_Framework_MockObject_MockObject {
        $repository = $this->createRepositoryStub(MetadataRepository::class, $metadataList);
        $repository->method('findByName')->willReturnCallback(function (string $name) use ($metadataList) {
            foreach ($metadataList as $metadata) {
                if ($metadata->getName() === $name) {
                    return $metadata;
                }
            }
            throw new EntityNotFoundException('Metadata', $name);
        });
        return $repository;
    }

    /** @return MetadataConstraintManager */
    protected function createMetadataConstraintManagerStub(array $namesToConstraintsMap): \PHPUnit_Framework_MockObject_MockObject {
        $stub = $this->createMock(MetadataConstraintManager::class);
        $stub->method('get')->willReturnCallback(function ($ruleName) use ($namesToConstraintsMap) {
            if (array_key_exists($ruleName, $namesToConstraintsMap)) {
                return $namesToConstraintsMap[$ruleName];
            } else {
                throw new \InvalidArgumentException("MetadataConstraintManager stub doesn't contain validator for '$ruleName'");
            }
        });
        return $stub;
    }

    /**
     * Make sure mocked rule doesn't have factory methods, such as forResourceKind(). These methods will return broken mocks by default,
     * which can produce false positives. If rule has factory methods, use createRuleWithFactoryMethodMock();
     */
    protected function createRuleMock(string $ruleClass, bool $result, ?string $exceptionMessage = null) {
        $mock = $this->createMock($ruleClass);
        $mock->method('validate')->willReturn($result);
        $mock->method('assert')->willReturnCallback(function () use ($result, $exceptionMessage) {
            if ($result) {
                return true;
            } else {
                throw new ValidationException($exceptionMessage);
            }
        });
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
        array $assigneeMetadataIds = []
    ): ResourceWorkflowPlace {
        $mock = $this->createMock(ResourceWorkflowPlace::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('getMissingRequiredMetadataIds')->willReturn($missingMetadataIds);
        $mock->method('resourceHasRequiredMetadata')->willReturn(empty($missingMetadataIds));
        $mock->method('restrictingMetadataIds')->willReturn(new FluentRestrictingMetadataSelector([], [], $assigneeMetadataIds));
        return $mock;
    }
}
