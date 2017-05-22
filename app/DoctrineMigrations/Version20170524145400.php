<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Wraps resource metadata values in arrays
 */
class Version20170524145400 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            $resource->wrapContents();
            $this->queueResourceUpdate($resource);
        }
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            $resource->unwrapContents();
            $this->queueResourceUpdate($resource);
        }
    }

    /**
     * Fetches all resources in an ORM-like fashion.
     * Doctrine Native Queries would be super-useful for this, but they rely on entity classes being located in adequate packages, while we
     * want to use entity class from this file.
     * @return Version20170524145400ResourceEntity[]
     */
    private function getResources(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id, contents FROM resource');
        return array_map(function ($row) {
            return new Version20170524145400ResourceEntity($row['id'], $row['contents']);
        }, $results);
    }

    private function queueResourceUpdate(Version20170524145400ResourceEntity $resource) {
        $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource->getAsParams());
    }
}

/**
 * A ResourceEntity dedicated to this migration.
 * Necessary because original ResourceEntity class may change, breaking the migration.
 * @see https://github.com/doctrine/migrations/issues/124
 */
class Version20170524145400ResourceEntity {
    private $id;
    private $contents;

    public function __construct(int $id, string $contentsJson) {
        $this->id = $id;
        $this->contents = (array)json_decode($contentsJson);
    }

    public function wrapContents(): void {
        foreach ($this->contents as &$value) {
            $value = [$value];
        }
    }

    public function unwrapContents(): void {
        $newContents = [];
        foreach ($this->contents as $key => $value) {
            if (count($value) > 0) {
                $newContents[$key] = $value[0];
            }
        }
        $this->contents = $newContents;
    }

    public function getAsParams(): array {
        return [
            'id' => $this->id,
            'contents' => json_encode($this->contents)
        ];
    }
}
