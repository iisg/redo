<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ResourcesFixture extends RepekaFixture {
    const ORDER = MetadataStage2Fixture::ORDER + UsersFixture::ORDER;
    const REFERENCE_DEPARTMENT_IET = 'resource-department-iet';
    const REFERENCE_USER_GROUP_ADMINS = 'resource-user-group-admins';
    const REFERENCE_USER_GROUP_SCANNERS = 'resource-user-group-scanners';
    const REFERENCE_USER_GROUP_SIGNED = 'resource-user-group-signed';
    const REFERENCE_RESOURCE_CATEGORY_EBOOKS = 'resource-category-ebooks';
    const REFERENCE_BOOK_1 = 'resource-book-1';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addUserGroups();
        $this->addVisibilityMetadataToGroups();
        $this->assignUsersToGroupsAndSetThemVisibility();
        $this->addVisibilityToUnauthenticatedUserResource();
        $this->addDictionaries();
        $this->addBooks();
        $this->addCmsPages();
        $this->addCmsReportRemark();
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
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
        $titleLanguageMetadataId = $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE_LANGUAGE)->getId();
        /** @var ResourceEntity $book1 */
        $book1 = $this->handleCommand(
            new ResourceCreateCommand(
                $bookResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_TITLE => [
                            [
                                'value' => 'PHP i MySQL',
                                'submetadata' => [
                                    $titleLanguageMetadataId => [['value' => 'PL'], ['value' => 'EN']],
                                ],
                            ],
                        ],
                        MetadataFixture::REFERENCE_METADATA_DESCRIPTION => ['Błędy młodości...'],
                        MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => [404],
                        MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => [$userScanner->getUserData()],
                        MetadataFixture::REFERENCE_METADATA_SUPERVISOR => [$userBudynek->getUserData()],
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                    ]
                ),
                $userAdmin
            ),
            self::REFERENCE_BOOK_1
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
                        MetadataFixture::REFERENCE_METADATA_CREATOR => [$userAdmin->getUserData()],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                    ]
                ),
                $userAdmin
            ),
            self::REFERENCE_RESOURCE_CATEGORY_EBOOKS
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $forbiddenBookResourceKind,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_TITLE => [
                            [
                                'value' => 'Python dla opornych',
                                'submetadata' => [
                                    $titleLanguageMetadataId => [
                                        ['value' => 'PL'],
                                    ],
                                ],
                            ],
                            [
                                'value' => 'Python for restive',
                                'submetadata' => [
                                    $titleLanguageMetadataId => [
                                        ['value' => 'EN'],
                                    ],
                                ],
                            ],
                        ],
                        MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT => $this->getReference(self::REFERENCE_DEPARTMENT_IET),
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
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
                        SystemMetadata::REPRODUCTOR => [$this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId()],
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
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
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                    ]
                ),
                $userAdmin
            )
        );
    }

    private function contents(array $data): ResourceContents {
        $contents = [];
        foreach ($data as $key => $values) {
            $metadataId = is_string($key) ? $this->getReference($key)->getId() : $key;
            $contents[$metadataId] = $values;
        }
        return ResourceContents::fromArray($contents);
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
        $this->handleCommand(
            new ResourceCreateCommand(
                $userGroupResourceKind,
                $this->contents(
                    [
                        SystemMetadata::USERNAME => ['Zalogowani'],
                    ]
                )
            ),
            self::REFERENCE_USER_GROUP_SIGNED
        );
    }

    private function addVisibilityMetadataToGroups() {
        /** @var ResourceEntity $userGroupAdmins */
        $userGroupAdmins = $this->getReference(self::REFERENCE_USER_GROUP_ADMINS);
        /** @var ResourceEntity $userGroupScanners */
        $userGroupScanners = $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS);
        /** @var ResourceEntity $userGroupSigned */
        $userGroupSigned = $this->getReference(self::REFERENCE_USER_GROUP_SIGNED);
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $userGroupAdmins,
                $userGroupAdmins->getContents()->withMergedValues(
                    SystemMetadata::VISIBILITY,
                    [$userGroupAdmins->getId()]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [$userGroupAdmins->getId(), $userGroupScanners->getId(), $userGroupSigned->getId()]
                )
            )
        );
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $userGroupScanners,
                $userGroupScanners->getContents()->withMergedValues(
                    SystemMetadata::VISIBILITY,
                    [$userGroupAdmins->getId()]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [$userGroupAdmins->getId(), $userGroupScanners->getId(), $userGroupSigned->getId()]
                )
            )
        );
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $userGroupSigned,
                $userGroupSigned->getContents()->withMergedValues(
                    SystemMetadata::VISIBILITY,
                    [$userGroupAdmins->getId()]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [$userGroupAdmins->getId(), $userGroupScanners->getId(), $userGroupSigned->getId()]
                )
            )
        );
    }

    private function assignUsersToGroupsAndSetThemVisibility() {
        /** @var ResourceEntity $admin */
        $admin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $admin,
                $admin->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )->withReplacedValues(
                    SystemMetadata::VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                    ]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )
            )
        );
        /** @var ResourceEntity $budynek */
        $budynek = $this->getReference(UsersFixture::REFERENCE_USER_BUDYNEK)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $budynek,
                $budynek->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_SCANNERS), $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)]
                )->withReplacedValues(
                    SystemMetadata::VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                    ]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )
            )
        );
        /** @var ResourceEntity $scanner */
        $scanner = $this->getReference(UsersFixture::REFERENCE_USER_SCANNER)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $scanner,
                $scanner->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_SCANNERS), $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)]
                )->withReplacedValues(
                    SystemMetadata::VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                    ]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )
            )
        );
        /** @var ResourceEntity $tester */
        $tester = $this->getReference(UsersFixture::REFERENCE_USER_TESTER)->getUserData();
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $tester,
                $tester->getContents()->withReplacedValues(
                    SystemMetadata::GROUP_MEMBER,
                    [$this->getReference(self::REFERENCE_USER_GROUP_SIGNED)]
                )->withReplacedValues(
                    SystemMetadata::VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                    ]
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )
            )
        );
    }

    private function addVisibilityToUnauthenticatedUserResource() {
        $resourceRepository = $this->container->get(ResourceRepository::class);
        $unauthenticatedUserResource = $resourceRepository->findOne(SystemResource::UNAUTHENTICATED_USER);
        $this->handleCommand(
            new ResourceUpdateContentsCommand(
                $unauthenticatedUserResource,
                $unauthenticatedUserResource->getContents()->withMergedValues(
                    SystemMetadata::VISIBILITY,
                    []
                )->withMergedValues(
                    SystemMetadata::TEASER_VISIBILITY,
                    [
                        $this->getReference(self::REFERENCE_USER_GROUP_ADMINS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS),
                        $this->getReference(self::REFERENCE_USER_GROUP_SIGNED),
                    ]
                )->withReplacedValues(
                    SystemMetadata::USERNAME,
                    'Niezalogowani'
                )
            )
        );
    }

    private function addCmsPages() {
        $cmsPageRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CMS_STATIC_PAGE);
        $cmsConfigRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CMS_CONFIG);
        $pageContent = '<h1>Nasz projekt jest super</h1> <repeka-version></repeka-version>';
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsPageRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_TITLE => ['O projekcie'],
                        MetadataFixture::REFERENCE_METADATA_CMS_CONTENT => $pageContent,
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SCANNERS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                            SystemResource::UNAUTHENTICATED_USER,
                        ],
                    ]
                )
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsConfigRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_ID => 'display_metadata_book',
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_VALUE => [
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getName(),
                        ],
                        MetadataFixture::REFERENCE_METADATA_CMS_CONTENT =>
                            'Metadane, które wyświetlą się na stronie szczegółów zasobu rodzaju "book"',
                    ]
                )
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsConfigRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_ID => 'display_metadata_DEFAULT',
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_VALUE => [
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE)->getName(),
                        ],
                        MetadataFixture::REFERENCE_METADATA_CMS_CONTENT =>
                            'Metadane, które wyświetlą się na stronie szczegółów zasobu '
                            . 'rodzajów nieposiadających własnej konfiguracji',
                    ]
                )
            )
        );
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsConfigRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_ID => 'display_metadata_book_bibtex',
                        MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_VALUE => [
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE_PDF)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_RELATED_BOOK)->getName(),
                            $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getName(),
                        ],
                        MetadataFixture::REFERENCE_METADATA_CMS_CONTENT =>
                            'Metadane, które wyświetlą się na stronie eksportu zasobu do formatu bibtex',
                    ]
                )
            )
        );
    }

    private function addCmsReportRemark() {
        $cmsRemarkRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_CMS_REMARKS);
        $this->handleCommand(
            new ResourceCreateCommand(
                $cmsRemarkRk,
                $this->contents(
                    [
                        MetadataFixture::REFERENCE_METADATA_CMS_TITLE => ['Uwagi'],
                        SystemMetadata::VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                        ],
                        SystemMetadata::TEASER_VISIBILITY => [
                            $this->getReference(self::REFERENCE_USER_GROUP_ADMINS)->getId(),
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                        ],
                        SystemMetadata::REPRODUCTOR => [
                            $this->getReference(self::REFERENCE_USER_GROUP_SIGNED)->getId(),
                        ],
                    ]
                )
            )
        );
    }
}
