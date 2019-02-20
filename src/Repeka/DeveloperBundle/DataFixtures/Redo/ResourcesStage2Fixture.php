<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourcesStage2Fixture extends RepekaFixture {
    const ORDER = ResourceWorkflowsStage2Fixture::ORDER + 1;

    /** @inheritdoc */
    public function load(ObjectManager $manager) {
        $book = $this->getReference(ResourcesFixture::REFERENCE_BOOK_1);
        $userAdmin = $this->getReference(AdminAccountFixture::REFERENCE_USER_ADMIN);
        $userScanner = $this->getReference(UsersFixture::REFERENCE_USER_SCANNER);
        $this->handleCommand(
            new ResourceTransitionCommand($book, $book->getContents(), 'e7d756ed-d6b3-4f2f-9517-679311e88b17', $userAdmin)
        );
        $this->handleCommand(
            new ResourceTransitionCommand($book, $book->getContents(), 'd3f73249-d10f-4d4b-8b63-be60b4c02081', $userScanner)
        );
    }
}
