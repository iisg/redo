<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Removes empty metadata value arrays from resources.
 */
class Version20170612233700 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            $resource->removeEmptyArrays();
            $this->queueResourceUpdate($resource);
        }
    }

    public function down(Schema $schema) {
        // Missing valueless metadata are okay. Doing nothing is a proper way of undoing this migration.
    }

    /** @return Version20170612233700ResourceEntity[] */
    private function getResources(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id, contents FROM resource');
        return array_map(function ($row) {
            return new Version20170612233700ResourceEntity($row['id'], $row['contents']);
        }, $results);
    }

    private function queueResourceUpdate(Version20170612233700ResourceEntity $resource) {
        $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource->getAsParams());
    }
}

/**
 * @see Version20170524145400ResourceEntity
 */
class Version20170612233700ResourceEntity {
    private $id;
    private $contents;

    public function __construct(int $id, string $contentsJson) {
        $this->id = $id;
        $this->contents = (array)json_decode($contentsJson);
    }

    public function removeEmptyArrays() {
        $this->contents = array_filter($this->contents, function ($values) {
            return count($values) > 0;
        });
    }

    public function getAsParams(): array {
        return [
            'id' => $this->id,
            'contents' => json_encode($this->contents)
        ];
    }
}
