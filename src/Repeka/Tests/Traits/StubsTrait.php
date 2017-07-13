<?php
namespace Repeka\Tests\Traits;

use Repeka\Application\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Validation\MetadataConstraintProvider;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Respect\Validation\Exceptions\ValidationException;

/**
 * @method \PHPUnit_Framework_MockObject_MockObject createMock(string $originalClassName)
 */
trait StubsTrait {
    protected function createLanguageRepositoryMock(array $languages): LanguageRepository {
        $mock = $this->createMock(LanguageRepository::class);
        $mock->method('getAvailableLanguageCodes')->willReturn($languages);
        return $mock;
    }

    protected function createMockEntity(string $className, int $id): \PHPUnit_Framework_MockObject_MockObject {
        $metadata = $this->createMock($className);
        $metadata->method('getId')->willReturn($id);
        return $metadata;
    }

    protected function createMetadataMock(int $id, ?int $baseId = null, string $control = 'dummy', array $constraints = []): Metadata {
        /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->createMockEntity(Metadata::class, $id);
        $metadata->method('getBaseId')->willReturn($baseId);
        $metadata->method('isBase')->willReturn($baseId === null);
        $metadata->method('getControl')->willReturn($control);
        $metadata->method('getConstraints')->willReturn($constraints);
        return $metadata;
    }

    /**
     * @param Metadata[] $metadataList
     * @return ResourceKind|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createResourceKindMock(array $metadataList = []): ResourceKind {
        $resourceKind = $this->createMock(ResourceKind::class);
        $resourceKind->method('getMetadataList')->willReturn($metadataList);
        return $resourceKind;
    }

    /** @return ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    protected function createResourceMock(int $id, ResourceKind $resourceKind, array $contents = []): ResourceEntity {
        $mock = $this->createMock(ResourceEntity::class);
        $mock->method('getKind')->willReturn($resourceKind);
        $mock->method('getId')->willReturn($id);
        $mock->method('getContents')->willReturn($contents);
        return $mock;
    }

    protected function createEntityLookupMap(array $entityList): array {
        $result = [];
        foreach ($entityList as $metadata) {
            $result[$metadata->getId()] = $metadata;
        }
        return $result;
    }

    protected function createRepositoryStub(string $repositoryClassName, array $entityList = []) {
        $entityList = array_values($entityList);
        $lookup = $this->createEntityLookupMap($entityList);
        $idCounter = 1;
        $repository = $this->createMock($repositoryClassName);
        $repository->method('findOne')->willReturnCallback(function ($id) use ($lookup, $repositoryClassName) {
            if (array_key_exists($id, $lookup)) {
                return $lookup[$id];
            } else {
                throw new EntityNotFoundException($repositoryClassName . 'Mock', $id);
            }
        });
        $repository->method('save')->willReturnCallback(function ($entity) use (&$idCounter) {
            if ($entity->getId() === null) {
                // Entities returned by save() method must have an ID assigned.
                EntityUtils::forceSetId($entity, $idCounter++);
            }
            return $entity;
        });
        return $repository;
    }

    protected function createMetadataConstraintProviderStub(array $namesToConstraintsMap): MetadataConstraintProvider {
        $stub = $this->createMock(MetadataConstraintProvider::class);
        $stub->method('get')->willReturnCallback(function ($ruleName) use ($namesToConstraintsMap) {
            if (array_key_exists($ruleName, $namesToConstraintsMap)) {
                return $namesToConstraintsMap[$ruleName];
            } else {
                throw new \InvalidArgumentException("MetadataConstraintProvider stub doesn't contain validator for '$ruleName'");
            }
        });
        return $stub;
    }

    protected function createEntityExistsMock(bool $result, ?string $exceptionMessage = null) {
        $stub = $this->createMock(EntityExistsRule::class);
        $stub->method('validate')->willReturn($result);
        if ($result) {
            $stub->method('assert')->willReturn(true);
        } else {
            $stub->method('assert')->willThrowException(new ValidationException($exceptionMessage));
        }
        $stub->method('forEntityType')->willReturnSelf();
        return $stub;
    }
}
