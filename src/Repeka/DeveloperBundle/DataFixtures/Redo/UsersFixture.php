<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\UseCase\User\UserCreateCommand;

class UsersFixture extends RepekaFixture {
    const ORDER = 1;

    const REFERENCE_USER_BUDYNEK = 'user-budynek';
    const REFERENCE_USER_TESTER = 'user-tester';
    const REFERENCE_USER_SCANNER = 'user-scanner';

    /** @inheritdoc */
    public function load(ObjectManager $manager) {
        $this->handleCommand(new UserCreateCommand('budynek', 'budynek'), self::REFERENCE_USER_BUDYNEK);
        $this->handleCommand(new UserCreateCommand('tester', 'tester'), self::REFERENCE_USER_TESTER);
        $this->handleCommand(new UserCreateCommand('skaner', 'skaner'), self::REFERENCE_USER_SCANNER);
    }
}
