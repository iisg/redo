<?php
namespace Repeka\Tests\Integration\Repository;

use Assert\Assertion;
use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Tests\IntegrationTestCase;

class ResourceWorkflowRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var EntityRepository|ResourceWorkflowRepository */
    private $workflowRepository;

    protected function setUp() {
        parent::setUp();
        $this->workflowRepository = $this->container->get(ResourceWorkflowRepository::class);
        $this->loadAllFixtures();
    }

    public function testFindsByMetadataDependency() {
        list($scanner, $supervisor) = $this->getScannerAndSupervisorMetadata();
        $supervisorDependants = $this->workflowRepository->findByAssigneeMetadata($supervisor);
        $this->assertEmpty($supervisorDependants);
        $scannerDependants = $this->workflowRepository->findByAssigneeMetadata($scanner);
        $this->assertCount(1, $scannerDependants);
    }

    public function testFindsByMetadataIdDependency() {
        /** @var Metadata $scanner */
        /** @var Metadata $supervisor */
        list($scanner, $supervisor) = $this->getScannerAndSupervisorMetadata();
        $supervisorDependants = $this->workflowRepository->findByAssigneeMetadata($supervisor->getId());
        $this->assertEmpty($supervisorDependants);
        $scannerDependants = $this->workflowRepository->findByAssigneeMetadata($scanner->getId());
        $this->assertCount(1, $scannerDependants);
    }

    /** @return Metadata[] */
    private function getScannerAndSupervisorMetadata(): array {
        /** @var Metadata[] $allMetadata */
        $allMetadata = $this->handleCommand(new MetadataListQuery());
        $scanner = $supervisor = null;
        foreach ($allMetadata as $metadata) {
            if ($metadata->getName() == 'Skanista') {
                $scanner = $metadata;
            } elseif ($metadata->getName() == 'Nadzorujący') {
                $supervisor = $metadata;
            }
        }
        Assertion::allNotNull([$scanner, $supervisor]);
        return [$scanner, $supervisor];
    }
}
