<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;

class RolesFixture extends RepekaFixture {
    const ORDER = 1;

    const ROLE_LIBRARY_WORKER = 'libraryWorkerRole';
    const ROLE_SCANNER = 'scannerRole';
    const ROLE_ACCEPTOR = 'acceptorRole';
    const ROLE_PUBLISHER = 'publisherRole';
    const ROLE_OCR = 'ocrRole';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Bibliotekarz', 'EN' => 'Library worker']), self::ROLE_LIBRARY_WORKER);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Skanista', 'EN' => 'Scanner']), self::ROLE_SCANNER);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Operator OCR', 'EN' => 'OCR operator']), self::ROLE_OCR);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Akceptujący', 'EN' => 'Acceptor']), self::ROLE_ACCEPTOR);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Publikujący', 'EN' => 'Publisher']), self::ROLE_PUBLISHER);
    }
}
