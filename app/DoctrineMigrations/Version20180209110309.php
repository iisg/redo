<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Get rid of metadata base extending mechanism.
 */
class Version20180209110309 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $resourceKindsMetadata = $connection->fetchAll('SELECT * FROM metadata WHERE resource_kind_id IS NOT NULL ORDER BY ordinal_number ASC');
        $metadataLists = [];
        foreach ($resourceKindsMetadata as $metadata) {
            $rkId = $metadata['resource_kind_id'];
            if (!isset($metadataLists[$rkId])) {
                $metadataLists[$rkId] = [];
            }
            $metadataLists[$rkId][] = [
                'id' => $metadata['base_id'],
                'label' => json_decode($metadata['label'] ?? '[]', true),
                'description' => json_decode($metadata['description'] ?? '[]', true),
                'placeholder' => json_decode($metadata['placeholder'] ?? '[]', true),
                'constraints' => json_decode($metadata['constraints'] ?? '[]', true),
            ];
        }
        $this->addSql('ALTER TABLE resource_kind ADD metadata_list JSONB NULL');
        foreach ($metadataLists as $rkId => $metadataList) {
            $metadataListJson = json_encode($metadataList);
            $this->addSql('UPDATE resource_kind SET metadata_list=:metadataList::JSONB WHERE id=:rkId', [
                'metadataList' => $metadataListJson,
                'rkId' => $rkId,
            ]);
        }
        $this->addSql('DELETE FROM metadata WHERE resource_kind_id IS NOT NULL');
        $this->addSql('ALTER TABLE metadata DROP CONSTRAINT fk_4f1434146dc0cb41');
        $this->addSql('DROP INDEX idx_4f1434146dc0cb41');
        $this->addSql('ALTER TABLE metadata DROP resource_kind_id');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }
}
