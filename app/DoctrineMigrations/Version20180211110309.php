<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Enhance Metadata table structure.
 */
class Version20180211110309 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('UPDATE metadata m1 SET resource_class = (SELECT resource_class FROM metadata m2 WHERE m2.id = m1.base_id) WHERE base_id IS NOT NULL');
        $this->addSql('UPDATE metadata m1 SET control = (SELECT control FROM metadata m2 WHERE m2.id = m1.base_id) WHERE control IS NULL');
        $this->addSql('UPDATE metadata m1 SET resource_class = \'\' WHERE resource_class IS NULL');
        $this->addSql('ALTER TABLE metadata ALTER control SET NOT NULL');
        $this->addSql('ALTER TABLE metadata ALTER resource_class SET NOT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }
}
