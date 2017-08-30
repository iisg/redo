<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Adds count constraints
 */
class Version20171016130000 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql(<<<SQL
UPDATE metadata
SET constraints = '{"count": {"min": 1, "max": 1}}'::JSONB || CASE
  WHEN constraints = '[]'::JSONB THEN '{}'::JSONB -- handle empty objects stored as arrays
  ELSE constraints
END
WHERE resource_kind_id IS NULL
SQL
        );
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("UPDATE metadata SET constraints = constraints - 'count'");
    }
}
