<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\EventListener\EvaluateDisplayStrategiesOnResourceCreationListener;
use Repeka\Domain\EventListener\UpdateDependentDisplayStrategiesListener;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class RandomResourcesFixture extends RepekaFixture {
    // if you want to populate your database with some random resources, increase these numbers and run npm run db:fixture
    // do not forget to revert them to zeros afterwards, as random resources are likely to break integration tests
    const RANDOM_BOOKS_COUNT = 0;
    // to create random tree instead of flat list, set to true
    const CREATE_RANDOM_TREE = false;

    const ORDER = ResourcesFixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        if (self::RANDOM_BOOKS_COUNT) {
            UpdateDependentDisplayStrategiesListener::$alwaysLeaveDirty = true;
            $this->addFakeBooks($manager);
        }
    }

    private function contents(array $data): ResourceContents {
        $contents = [];
        foreach ($data as $key => $values) {
            $metadataId = is_string($key) ? $this->getReference($key)->getId() : $key;
            $contents[$metadataId] = $values;
        }
        return ResourceContents::fromArray($contents);
    }

    private function addFakeBooks(ObjectManager $manager) {
        $faker = Factory::create();
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var UserEntity $userAdmin */
        $userAdmin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN);
        /** @var UserEntity $userBudynek */
        $userBudynek = $this->getReference(UsersFixture::REFERENCE_USER_BUDYNEK);
        $possibleUsers = [[], $userAdmin->getUserData(), $userBudynek->getUserData()];
        /** @var ResourceEntity $categoryResource */
        $categoryResource = $this->getReference(ResourcesFixture::REFERENCE_RESOURCE_CATEGORY_EBOOKS);
        $createdBookIds = [$categoryResource->getId()];
        for ($i = 0; $i < self::RANDOM_BOOKS_COUNT; $i++) {
            /** @var ResourceEntity $book */
            $contents = [
                            MetadataFixture::REFERENCE_METADATA_TITLE => $faker->catchPhrase,
                            MetadataFixture::REFERENCE_METADATA_DESCRIPTION => $faker->realText(),
                            MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES => $faker->numberBetween(10, 1000),
                            MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER => $faker->randomElement($possibleUsers),
                            MetadataFixture::REFERENCE_METADATA_SUPERVISOR => $faker->randomElement($possibleUsers),
                            MetadataFixture::REFERENCE_METADATA_CREATOR => $faker->randomElement($possibleUsers),
            ];
            if (self::CREATE_RANDOM_TREE) {
                $randomParentId = $createdBookIds[array_rand($createdBookIds)];
                $contents[SystemMetadata::PARENT] = $randomParentId;
            }
            $book = $this->handleCommand(
                new ResourceCreateCommand(
                    $bookResourceKind,
                    $this->contents($contents)
                )
            );
            $createdBookIds[] = $book->getId();
            $place = $faker->randomElement(
                [
                    'y1oosxtgf',
                    'lb1ovdqcy',
                    'qqd3yk499',
                    '9qq9ipqa3',
                    'ss9qm7r78',
                    'jvz160sl4',
                    'xo77kutzk',
                    'j70hlpsvu',
                ]
            );
            $book->setMarking([$place => true]);
            $manager->getRepository(ResourceEntity::class)->save($book);
        }
    }
}
