<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourcesFixture extends RepekaFixture {
    const ORDER = ResourceKindsStage2Fixture::ORDER + UsersFixture::ORDER;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var ResourceKind $forbiddenBookResourceKind */
        $forbiddenBookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        /** @var ResourceKind $categoryResourceKind */
        $categoryResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CATEGORY);
        /** @var UserEntity $userBudynek */
        $userBudynek = $this->getReference(UsersFixture::REFERENCE_USER_BUDYNEK);
        /** @var UserEntity $userScanner */
        $userScanner = $this->getReference(UsersFixture::REFERENCE_USER_SCANNER);
        /** @var ResourceEntity $book1 */
        $book1 = $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_TITLE => ['PHP i MySQL'],
            MetadataFixture::REFERENCE_METADATA_DESCRIPTION => ['Błędy młodości...'],
            MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [404],
            MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => [$userScanner->getUserData()],
            MetadataFixture::REFERENCE_METADATA_SUPERVISOR => [$userBudynek->getUserData()],
        ])));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_TITLE => ['PHP - to można leczyć!'],
            MetadataFixture::REFERENCE_METADATA_DESCRIPTION =>
                ['Poradnik dla cierpiących na zwyrodnienie interpretera.'],
            MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [1337],
            MetadataFixture::REFERENCE_METADATA_SEE_ALSO => [$book1],
            MetadataStage2Fixture::REFERENCE_METADATA_RELATED_BOOK => [$book1],
        ])));
        $this->handleCommand(new ResourceCreateCommand($forbiddenBookResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_TITLE => ['Python dla opornych'],
        ])));
        /** @var ResourceEntity $ebooks */
        $ebooks = $this->handleCommand(new ResourceCreateCommand($categoryResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME => ['E-booki'],
        ])));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            SystemMetadata::PARENT => [$ebooks->getId()],
            MetadataFixture::REFERENCE_METADATA_TITLE => ['"Mogliśmy użyć Webpacka" i inne spóźnione mądrości'],
        ])));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            SystemMetadata::PARENT => [$ebooks->getId()],
            MetadataFixture::REFERENCE_METADATA_TITLE => ['Pair programming: jak równocześnie pisać na jednej klawiaturze w dwie osoby'],
        ])));
        $userAdmin = $manager->getRepository(UserEntity::class)->loadUserByUsername('admin');
        $this->handleCommand(new ResourceTransitionCommand($book1, 'e7d756ed-d6b3-4f2f-9517-679311e88b17', $userAdmin));
        $this->handleCommand(new ResourceTransitionCommand($book1, 'd3f73249-d10f-4d4b-8b63-be60b4c02081', $userScanner));
    }

    private function contents(array $data): ResourceContents {
        $contents = [];
        foreach ($data as $key => $values) {
            $metadataId = is_string($key) ? $this->getReference($key)->getId() : $key;
            $contents[$metadataId] = $values;
        }
        return ResourceContents::fromArray($contents);
    }
}
