<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\ResourceContents;

/**
 * Clean resource contents from empty metadata.
 */
class Version20180424133832 extends RepekaMigration {
    public function migrate() {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $resources = $connection->fetchAll('SELECT id, contents FROM resource');
        foreach ($resources as $resource) {
            $resource['contents'] = json_decode($resource['contents'], true);
            $resource['contents'] = ResourceContents::fromArray($resource['contents'])->filterOutEmptyMetadata()->toArray();
            $resource['contents'] = json_encode($resource['contents']);
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource);
        }
    }
}
