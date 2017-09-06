<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add lockedMetadataIds to workflow places
 */
class Version20170906113103 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        // Postgres 9.4 won't alter column type to JSONB if default value is JSON (or the other way)
        $this->addSql('ALTER TABLE workflow ALTER places DROP DEFAULT');
        $this->addSql('ALTER TABLE workflow ALTER transitions DROP DEFAULT');
        $this->addSql('ALTER TABLE workflow ALTER places TYPE JSONB USING places::JSONB');
        $this->addSql('ALTER TABLE workflow ALTER transitions TYPE JSONB USING transitions::JSONB');
        $this->addSql("ALTER TABLE workflow ALTER places SET DEFAULT '[]'::JSONB");
        $this->addSql("ALTER TABLE workflow ALTER transitions SET DEFAULT '[]'::JSONB");
        $workflows = $this->getWorkflows();
        foreach ($workflows as $workflow) {
            $workflow->addLockedMetadataIds();
            $this->queueWorkflowUpdate($workflow);
        }
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE workflow ALTER places DROP DEFAULT');
        $this->addSql('ALTER TABLE workflow ALTER transitions DROP DEFAULT');
        $this->addSql('ALTER TABLE workflow ALTER places TYPE JSON USING places::JSON');
        $this->addSql('ALTER TABLE workflow ALTER transitions TYPE JSON USING transitions::JSON');
        $this->addSql("ALTER TABLE workflow ALTER places SET DEFAULT '[]'::JSON");
        $this->addSql("ALTER TABLE workflow ALTER transitions SET DEFAULT '[]'::JSON");
        $workflows = $this->getWorkflows();
        foreach ($workflows as $workflow) {
            $workflow->removeLockedMetadataIds();
            $this->queueWorkflowUpdate($workflow);
        }
    }

    /** @return Version20170906113103WorkflowEntity[] */
    private function getWorkflows(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id, places FROM workflow');
        return array_map(function ($row) {
            return new Version20170906113103WorkflowEntity($row['id'], $row['places']);
        }, $results);
    }

    private function queueWorkflowUpdate(Version20170906113103WorkflowEntity $workflow): void {
        $this->addSql('UPDATE workflow SET places = :places WHERE id = :id', $workflow->getAsParams());
    }
}

class Version20170906113103WorkflowEntity {
    private $id;
    private $places;

    public function __construct(int $id, string $placesJson) {
        $this->id = $id;
        $this->places = (array)json_decode($placesJson);
    }

    public function addLockedMetadataIds(): void {
        foreach ($this->places as $place) {
            $place->lockedMetadataIds = $place->lockedMetadataIds ?? [];
        }
    }

    public function removeLockedMetadataIds(): void {
        foreach ($this->places as $place) {
            unset($place->lockedMetadataIds);
        }
    }

    public function getAsParams(): array {
        return [
            'id' => $this->id,
            'places' => json_encode($this->places),
        ];
    }
}
