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
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ResourcesFixture extends RepekaFixture {
    const ORDER = MetadataStage2Fixture::ORDER + UsersFixture::ORDER;
    const REFERENCE_DEPARTMENT_IET = 'resource-department-iet';
    const REFERENCE_USER_GROUP_ADMINS = 'resource-user-group-admins';
    const REFERENCE_USER_GROUP_SCANNERS = 'resource-user-group-scanners';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addUserGroups();
        $this->assignUsersToGroups();
        $this->addDictionaries();
        $this->addBooks();
        $this->addCmsPages();
    }

    private function addDictionaries() {
        $departmentResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT);
        $universityResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY);
        $publishingHouseResouceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_PUBLISHING_HOUSE);
        $userAdmin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN);
        $agh = $this->handleCommand(
            new ResourceCreateCommand(
                $universityResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Akademia Górniczo Hutnicza',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'AGH',
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $universityResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Politechnika Krakowska',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'PK',
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $departmentResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Informatyki, Elektroniki i Telekomunikacji',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'IET',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY => $agh,
                    ]
                ),
                $userAdmin
            ),
            self::REFERENCE_DEPARTMENT_IET
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $departmentResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Elektroniki, Automatyki i Inżynierii Biomedycznej',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV => 'EAIB',
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY => $agh,
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $publishingHouseResouceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME => 'Wydawnictwo Zakładu Narodowego im. Ossolińskich',
                    ]
                ),
                $userAdmin
            )
        );
    }

    private function addBooks() {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var ResourceKind $forbiddenBookResourceKind */
        $forbiddenBookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        /** @var ResourceKind $categoryResourceKind */
        $categoryResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CATEGORY);
        $userAdmin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN);
        /** @var UserEntity $userBudynek */
        $userBudynek = $this->getReference(UsersFixture::REFERENCE_USER_BUDYNEK);
        /** @var UserEntity $userScanner */
        $userScanner = $this->getReference(UsersFixture::REFERENCE_USER_SCANNER);
        /** @var ResourceEntity $book1 */
        $book1 = $this->handleCommand(
            new ResourceCreateCommand(
                $bookResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_TITLE => ['PHP i MySQL'],
                        MetadataFixture::REFERENCE_METADATA_DESCRIPTION => ['Błędy młodości...'],
                        MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [404],
                        MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => [$userScanner->getUserData()],
                        MetadataFixture::REFERENCE_METADATA_SUPERVISOR => [$userBudynek->getUserData()],
                    ]
                ),
                $userAdmin
            )
        );
        $urlLabelMetadataId = $this->getReference(MetadataFixture::REFERENCE_METADATA_URL_LABEL)->getId();
        $this->handleCommand(
            new ResourceCreateCommand(
                $bookResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_TITLE => ['PHP - to można leczyć!'],
                        MetadataFixture::REFERENCE_METADATA_DESCRIPTION =>
                            ['Poradnik dla cierpiących na zwyrodnienie interpretera.'],
                        MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [1337],
                        MetadataFixture::REFERENCE_METADATA_SEE_ALSO => [$book1],
                        MetadataFixture::REFERENCE_METADATA_RELATED_BOOK => [$book1],
                        MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => [$userScanner->getUserData()],
                        MetadataFixture::REFERENCE_METADATA_SUPERVISOR => [$userBudynek->getUserData()],
                        MetadataFixture::REFERENCE_METADATA_HARD_COVER => [true],
                        MetadataFixture::REFERENCE_METADATA_URL => [
                            [
                                'value' => 'http://google.pl',
                                'submetadata' => [$urlLabelMetadataId => 'Tam znajdziesz więcej'],
                            ],
                            [
                                'value' => 'https://duckduckgo.com',
                                'submetadata' => [
                                    $urlLabelMetadataId => 'Tam znajdziesz więcej ale inni nie dowiedzą się, że interesujesz się PHP',
                                ],
                            ],
                        ],
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $forbiddenBookResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_TITLE => ['Python dla opornych'],
                        MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT => $this->getReference(self::REFERENCE_DEPARTMENT_IET),
                    ]
                ),
                $userAdmin
            )
        );
        /** @var ResourceEntity $ebooks */
        $ebooks = $this->handleCommand(
            new ResourceCreateCommand(
                $categoryResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME => ['E-booki'],
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $bookResourceKind,
                $this->contents(
                    [
                        SystemMetadata::PARENT => [$ebooks->getId()],
                        MetadataFixture::REFERENCE_METADATA_TITLE => ['"Mogliśmy użyć Webpacka" i inne spóźnione mądrości'],
                    ]
                ),
                $userAdmin
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $bookResourceKind,
                $this->contents(
                    [
                        SystemMetadata::PARENT => [$ebooks->getId()],
                        MetadataFixture::REFERENCE_METADATA_TITLE => [
                            'Pair programming: jak równocześnie pisać na jednej klawiaturze w dwie osoby',
                        ],
                    ]
                ),
                $userAdmin
            )
        );
        $this->makeTransition([$book1], [$userAdmin, $userScanner]);
    }

    private function contents(array $data): ResourceContents {
        $contents = [];
        foreach ($data as $key => $values) {
            $metadataId = is_string($key) ? $this->getReference($key)->getId() : $key;
            $contents[$metadataId] = $values;
        }
        return ResourceContents::fromArray($contents);
    }

    private function makeTransition(array $books, array $executors) {
        $this->handleCommand(
            new ResourceTransitionCommand($books[0], $books[0]->getContents(), 'e7d756ed-d6b3-4f2f-9517-679311e88b17', $executors[0])
        );
        $this->handleCommand(
            new ResourceTransitionCommand($books[0], $books[0]->getContents(), 'd3f73249-d10f-4d4b-8b63-be60b4c02081', $executors[1])
        );
    }

    private function addUserGroups() {
        /** @var ResourceKind $userGroupResourceKind */
        $userGroupResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP);
        $this->handleCommand(
            new ResourceCreateCommand(
                $userGroupResourceKind,
                $this->contents(
                    [
                        SystemMetadata::USERNAME => ['Administratorzy'],
                    ]
                )
            ),
            self::REFERENCE_USER_GROUP_ADMINS
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $userGroupResourceKind,
                $this->contents(
                    [
                        SystemMetadata::USERNAME => ['Skaniści'],
                    ]
                )
            ),
            self::REFERENCE_USER_GROUP_SCANNERS
        );
    }

    private function assignUsersToGroups() {
        /** @var ResourceEntity $admin */
        $admin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $admin,
                $admin->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_ADMINS), $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)]
                )
            )
        );
        $budynek = $this->getReference(UsersFixture::REFERENCE_USER_BUDYNEK)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $budynek,
                $budynek->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)]
                )
            )
        );
        $scanner = $this->getReference(UsersFixture::REFERENCE_USER_SCANNER)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $scanner,
                $scanner->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)]
                )
            )
        );
    }

    private function addCmsPages() {
        $cmsPageRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CMS_STATIC_PAGE);
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsPageRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_TITLE => ['O projekcie'],
                        MetadataFixture::REFERENCE_METADATA_CMS_CONTENT =>
                            '<h1>Nasz projekt jest super</h1> <repeka-version></repeka-version>',
                    ]
                )
            )
        );
    }
}
