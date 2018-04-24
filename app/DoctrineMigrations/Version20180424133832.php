<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Entity\ResourceContents;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Clean resource contents from empty metadata.
 */
class Version20180424133832 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $resources = $connection->fetchAll('SELECT id, contents FROM resource');
        foreach ($resources as $resource) {
            $resource['contents'] = json_decode($resource['contents'], true);
            $resource['contents'] = ResourceContents::fromArray($resource['contents'])->filterOutEmptyMetadata()->toArray();
            $resource['contents'] = json_encode($resource['contents']);
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource);
        }
    }

    public function down(Schema $schema) {
        throw new \RuntimeException('There is no way back.');
    }
}
