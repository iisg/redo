<?php

namespace Repeka\Application\Command\Initialization;

use Repeka\Application\Command\TransactionalCommand;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\UserRoleRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemUserRolesCommand extends TransactionalCommand {
    /** @var UserRoleRepository */
    private $userRoleRepository;
    /** @var LanguageRepository */
    private $languageRepository;
    /** @var EntityIdGeneratorHelper */
    private $idGeneratorHelper;

    public function __construct(
        EntityIdGeneratorHelper $idGeneratorHelper,
        UserRoleRepository $userRoleRepository,
        LanguageRepository $languageRepository
    ) {
        parent::__construct();
        $this->idGeneratorHelper = $idGeneratorHelper;
        $this->userRoleRepository = $userRoleRepository;
        $this->languageRepository = $languageRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:initialize:system-user-roles')
            ->setDescription('Inserts system user roles.');
    }

    /** @inheritdoc */
    protected function executeInTransaction(InputInterface $input, OutputInterface $output) {
        $firstLanguageCode = strtoupper($this->languageRepository->getAvailableLanguageCodes()[0]);
        $this->idGeneratorHelper->preventGeneratingIds(UserRole::class);
        $this->addSystemUserRoles($output, $firstLanguageCode);
        $this->idGeneratorHelper->restoreIdGenerator(UserRole::class, 'role_id_seq');
    }

    public function addSystemUserRoles(OutputInterface $output, $defualtLabelLanguageCode) {
        foreach (SystemUserRole::toArray() as $roleName => $roleId) {
            if (!$this->userRoleRepository->exists($roleId)) {
                $systemUserRole = new SystemUserRole($roleId);
                $userRole = $systemUserRole->toUserRole();
                $userRole->update([$defualtLabelLanguageCode => ucfirst(strtolower($roleName))]);
                $this->userRoleRepository->save($userRole);
                $output->writeln("Role $roleName has been created.");
            } else {
                $output->writeln("Role $roleName already exists.");
            }
        }
    }
}
