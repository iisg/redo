<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Entity\EntityUtils;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemUserResourceInitializer extends ApplicationInitializer {

    private $classMetadata;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->classMetadata = $entityManager->getClassMetadata(ResourceKind::class);
    }

    public function initialize(OutputInterface $output, ContainerInterface $container) {
        $this->addUserResourceKind($output, $container);
    }

    private function addUserResourceKind(OutputInterface $output, ContainerInterface $container) {
        /** @var ResourceKindRepository $resourceKindRepository */
        $resourceKindRepository = $container->get('doctrine')->getRepository(ResourceKind::class);
        foreach (SystemResourceKind::toArray() as $userResourceKindName => $userResourceKindId) {
            if (!$resourceKindRepository->exists($userResourceKindId)) {
                $systemUserResourceKind = new SystemResourceKind($userResourceKindId);
                $resourceKind = new ResourceKind([]);
                EntityUtils::forceSetId($resourceKind, $systemUserResourceKind->getValue());
                $label = [];
                $label['PL'] = $label['EN'] = strtolower($userResourceKindName);
                $resourceKind->update($label, []);
                $resourceKindRepository->save($resourceKind);
                $output->writeln('User resource created.');
            }
        }
    }

    public function preEntityInitializer() {
        $this->preventGeneratingIds($this->classMetadata);
    }

    public function postEntityInitializer() {
        $this->restoreIdGenerator($this->classMetadata, 'resource_kind_id_seq');
    }
}
