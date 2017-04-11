<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemUserRolesInitializer implements ApplicationInitializer {
    public function initialize(OutputInterface $output, ContainerInterface $container) {
        $userRolesRepository = $container->get('doctrine')->getRepository(UserRole::class);
        $defaultLanguage = $container->getParameter('repeka.default_ui_language');
        $this->preventGeneratingUserRoleUids($container);
        $this->addSystemUserRoles($output, $userRolesRepository, $defaultLanguage);
        $this->restoreUuidGenerator($container);
    }

    public function addSystemUserRoles(OutputInterface $output, UserRoleRepository $userRolesRepository, string $defaultLanguage) {
        foreach (SystemUserRole::toArray() as $roleName => $roleId) {
            if (!$userRolesRepository->exists($roleId)) {
                $language = strtoupper($defaultLanguage);
                $userRole = (new SystemUserRole($roleId))->toUserRole();
                $userRole->update([$language => ucfirst(strtolower($roleName))]);
                $userRolesRepository->save($userRole);
                $output->writeln('Role created: ' . $roleName);
            }
        }
    }

    /**
     * By default, Doctrine overrides manually set ids with the generated ones for new entities.
     * This behaviour should be overridden for now because we set ids manually.
     *
     * @see http://stackoverflow.com/a/17587008/878514
     */
    private function preventGeneratingUserRoleUids(ContainerInterface $container) {
        $metadata = $container->get('doctrine')->getManager()->getClassMetaData(UserRole::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }

    private function restoreUuidGenerator($container) {
        $metadata = $container->get('doctrine')->getManager()->getClassMetaData(UserRole::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_UUID);
        $metadata->setIdGenerator(new UuidGenerator());
    }
}
