<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;

class RolesFixture extends RepekaFixture {
    const ORDER = 1;

    const ROLE_ACCEPTOR = 'acceptorRole';
    const ROLE_LIBRARIAN = 'librarianRole';
    const ROLE_OCR = 'ocrRole';
    const ROLE_PUBLISHER = 'publisherRole';
    const ROLE_SCANNER = 'scannerRole';
    const ROLE_TESTER = 'testerRole';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Akceptujący', 'EN' => 'Acceptor']), self::ROLE_ACCEPTOR);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Bibliotekarz', 'EN' => 'Librarian']), self::ROLE_LIBRARIAN);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Operator OCR', 'EN' => 'OCR operator']), self::ROLE_OCR);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Publikujący', 'EN' => 'Publisher']), self::ROLE_PUBLISHER);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Skanista', 'EN' => 'Scanner']), self::ROLE_SCANNER);
        $this->handleCommand(new UserRoleCreateCommand(['PL' => 'Tester', 'EN' => 'Tester']), self::ROLE_TESTER);
    }
}
