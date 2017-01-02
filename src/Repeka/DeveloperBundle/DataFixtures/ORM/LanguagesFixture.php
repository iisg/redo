<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;

class LanguagesFixture extends RepekaFixture {
    const ORDER = 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->handleCommand(LanguageCreateCommand::fromArray([
            'code' => 'PL',
            'flag' => 'PL',
            'name' => 'polski',
        ]));
        $this->handleCommand(LanguageCreateCommand::fromArray([
            'code' => 'EN',
            'flag' => 'GB',
            'name' => 'angielski',
        ]));
    }

    public function getOrder() {
        return self::ORDER;
    }
}
