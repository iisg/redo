<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add assigneeMetadataIds to workflow places
 */
class Version20170914113000 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $workflows = $this->getWorkflows();
        foreach ($workflows as $workflow) {
            $workflow->addAssigneeMetadataIds();
            $this->queueWorkflowUpdate($workflow);
        }
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $workflows = $this->getWorkflows();
        foreach ($workflows as $workflow) {
            $workflow->removeAssigneeMetadataIds();
            $this->queueWorkflowUpdate($workflow);
        }
    }

    /** @return Version20170914113000WorkflowEntity[] */
    private function getWorkflows(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id, places FROM workflow');
        return array_map(function ($row) {
            return new Version20170914113000WorkflowEntity($row['id'], $row['places']);
        }, $results);
    }

    private function queueWorkflowUpdate(Version20170914113000WorkflowEntity $workflow): void {
        $this->addSql('UPDATE workflow SET places = :places WHERE id = :id', $workflow->getAsParams());
    }
}

class Version20170914113000WorkflowEntity {
    private $id;
    private $places;

    public function __construct(int $id, string $placesJson) {
        $this->id = $id;
        $this->places = (array)json_decode($placesJson);
    }

    public function addAssigneeMetadataIds(): void {
        foreach ($this->places as $place) {
            $place->assigneeMetadataIds = $place->assigneeMetadataIds ?? [];
        }
    }

    public function removeAssigneeMetadataIds(): void {
        foreach ($this->places as $place) {
            unset($place->assigneeMetadataIds);
        }
    }

    public function getAsParams(): array {
        return [
            'id' => $this->id,
            'places' => json_encode($this->places),
        ];
    }
}
