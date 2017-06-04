<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourcesFixture extends RepekaFixture {
    const ORDER = ResourceKindsStage2Fixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var ResourceKind $forbiddenBookResourceKind */
        $forbiddenBookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        /** @var ResourceEntity $book1 */
        $book1 = $this->handleCommand(new ResourceCreateCommand($bookResourceKind, [
            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId() => ['PHP i MySQL'],
            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId() => ['Błędy młodości...'],
            $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId() => [404]
        ]));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, [
            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId() => ['PHP - to można leczyć!'],
            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId() =>
                ['Poradnik dla cierpiących na zwyrodnienie interpretera.'],
            $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId() => [1337],
            $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId() => [$book1],
            $this->getReference(MetadataStage2Fixture::REFERENCE_METADATA_RELATED_BOOK)->getId() => [$book1],
        ]));
        $this->handleCommand(new ResourceCreateCommand($forbiddenBookResourceKind, [
            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId() => ['Python dla opornych'],
        ]));
        $adminUser = $manager->getRepository(UserEntity::class)->findBy(['username' => 'admin'])[0];
        $this->handleCommand(new ResourceTransitionCommand($book1, 'e7d756ed-d6b3-4f2f-9517-679311e88b17', $adminUser));
        $this->handleCommand(new ResourceTransitionCommand($book1, 'd3f73249-d10f-4d4b-8b63-be60b4c02081', $adminUser));
    }
}
