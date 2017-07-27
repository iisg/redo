<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemResourceClass;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add resource class for resource, metadata, resource_kind, workflow
 */
class Version20170829182414 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE resource ADD resource_class VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_kind ADD resource_class VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE metadata ADD resource_class VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE workflow ADD resource_class VARCHAR(64) DEFAULT NULL');
        // update user's data
        $this->addSql('UPDATE resource SET resource_class = :systemResourceClass WHERE resource.kind_id = :resourceKind',
            ['systemResourceClass' => SystemResourceClass::USER, 'resourceKind' => SystemResourceKind::USER]);
        $this->addSql('UPDATE resource_kind SET resource_class = :systemResourceClass WHERE resource_kind.id = :resourceKind',
            ['systemResourceClass' => SystemResourceClass::USER, 'resourceKind' => SystemResourceKind::USER]);
        $this->addSql('UPDATE metadata SET resource_class = :systemResourceClass WHERE metadata.resource_kind_id = :resourceKind',
            ['systemResourceClass' => SystemResourceClass::USER, 'resourceKind' => SystemResourceKind::USER]);
        // update rest of data
        $resourceClass = $this->container->getParameter('repeka.resource_classes')[0];
        $this->addSql('UPDATE resource SET resource_class = :resourceClass WHERE resource.resource_class IS NULL',
            ['resourceClass' => $resourceClass]);
        $this->addSql('UPDATE resource_kind SET resource_class = :resourceClass WHERE resource_kind.resource_class IS NULL',
            ['resourceClass' => $resourceClass]);
        $this->addSql('UPDATE metadata SET resource_class = :resourceClass WHERE metadata.id > 0 AND metadata.resource_class IS NULL',
            ['resourceClass' => $resourceClass]);
        $this->addSql('UPDATE metadata SET resource_class = :resourceClass WHERE metadata.id = :usernameMetadataId',
            ['resourceClass' => SystemResourceClass::USER, 'usernameMetadataId' => SystemMetadata::USERNAME]);
        $this->addSql('UPDATE workflow SET resource_class = :resourceClass',
            ['resourceClass' => $resourceClass]);
        $this->addSql('ALTER TABLE resource ALTER resource_class SET NOT NULL');
        $this->addSql('ALTER TABLE resource_kind ALTER resource_class SET NOT NULL');
        $this->addSql('ALTER TABLE workflow ALTER resource_class SET NOT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE resource DROP resource_class');
        $this->addSql('ALTER TABLE resource_kind DROP resource_class');
        $this->addSql('ALTER TABLE metadata DROP resource_class');
        $this->addSql('ALTER TABLE workflow DROP resource_class');
    }
}
