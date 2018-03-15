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
    const REFERENCE_DEPARTMENT_IET = 'resource-department-iet';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addDictionaries();
        $this->addBooks($manager);
    }

    private function addDictionaries() {
        $departmentResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT);
        $universityResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY);
        $agh = $this->handleCommand(new ResourceCreateCommand($universityResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Akademia Górniczo Hutnicza',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'AGH',
        ])));
        $this->handleCommand(new ResourceCreateCommand($universityResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Politechnika Krakowska',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'PK',
        ])));
        $this->handleCommand(new ResourceCreateCommand($departmentResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Informatyki, Elektroniki i Telekomunikacji',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'IET',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY => $agh,
        ])), self::REFERENCE_DEPARTMENT_IET);
        $this->handleCommand(new ResourceCreateCommand($departmentResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Elektroniki, Automatyki i Inżynierii Biomedycznej',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'EAIB',
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY => $agh,
        ])));
    }

    private function addBooks(ObjectManager $manager) {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var ResourceKind $forbiddenBookResourceKind */
        $forbiddenBookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        /** @var ResourceKind $categoryResourceKind */
        $categoryResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CATEGORY);
        $userAdmin = $manager->getRepository(UserEntity::class)->loadUserByUsername('admin');
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
        ]), $userAdmin));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_TITLE => ['PHP - to można leczyć!'],
            MetadataFixture::REFERENCE_METADATA_DESCRIPTION =>
                ['Poradnik dla cierpiących na zwyrodnienie interpretera.'],
            MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [1337],
            MetadataFixture::REFERENCE_METADATA_SEE_ALSO => [$book1],
            MetadataFixture::REFERENCE_METADATA_RELATED_BOOK => [$book1],
            MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => [$userScanner->getUserData()],
            MetadataFixture::REFERENCE_METADATA_SUPERVISOR => [$userBudynek->getUserData()],
        ]), $userAdmin));
        $this->handleCommand(new ResourceCreateCommand($forbiddenBookResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_TITLE => ['Python dla opornych'],
            MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT => $this->getReference(self::REFERENCE_DEPARTMENT_IET),
        ])));
        /** @var ResourceEntity $ebooks */
        $ebooks = $this->handleCommand(new ResourceCreateCommand($categoryResourceKind, $this->contents([
            MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME => ['E-booki'],
        ])));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            SystemMetadata::PARENT => [$ebooks->getId()],
            MetadataFixture::REFERENCE_METADATA_TITLE => ['"Mogliśmy użyć Webpacka" i inne spóźnione mądrości'],
        ]), $userAdmin));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, $this->contents([
            SystemMetadata::PARENT => [$ebooks->getId()],
            MetadataFixture::REFERENCE_METADATA_TITLE => ['Pair programming: jak równocześnie pisać na jednej klawiaturze w dwie osoby'],
        ]), $userAdmin));
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
