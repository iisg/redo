<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemUserRolesInitializer extends ApplicationInitializer {

    private $classMetadata;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->classMetadata = $entityManager->getClassMetadata(UserRole::class);
    }

    public function initialize(OutputInterface $output, ContainerInterface $container) {
        $userRolesRepository = $container->get('doctrine')->getRepository(UserRole::class);
        $defaultLanguage = $container->getParameter('repeka.default_ui_language');
        $this->addSystemUserRoles($output, $userRolesRepository, $defaultLanguage);
    }

    public function addSystemUserRoles(OutputInterface $output, UserRoleRepository $userRolesRepository, string $defaultLanguage) {
        foreach (SystemUserRole::toArray() as $roleName => $roleId) {
            if (!$userRolesRepository->exists($roleId)) {
                $language = strtoupper($defaultLanguage);
                $systemUserRole = new SystemUserRole($roleId);
                $userRole = $systemUserRole->toUserRole();
                $userRole->update([$language => ucfirst(strtolower($roleName))]);
                $userRolesRepository->save($userRole);
                $output->writeln('Role created: ' . $roleName);
            }
        }
    }

    public function preEntityInitializer() {
        $this->preventGeneratingIds($this->classMetadata);
    }

    public function postEntityInitializer() {
        $this->restoreUuidGenerator($this->classMetadata);
    }
}
