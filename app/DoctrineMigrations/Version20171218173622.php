<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Adds maxCount constraints
 */
class Version20171218173622 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql(<<<SQL
UPDATE metadata
SET constraints = jsonb_build_object('maxCount', constraints #> '{count,max}') || constraints
WHERE JSONB_EXISTS(constraints, 'count') = TRUE
SQL
        );
        $this->addSql("UPDATE metadata SET constraints = constraints - 'count'");
        $this->addSql(<<<SQL
UPDATE metadata
SET constraints = '{"maxCount": 0}'::JSONB || CASE
  WHEN constraints = '[]'::JSONB THEN '{}'::JSONB -- handle empty objects stored as arrays
  ELSE constraints
END
WHERE resource_kind_id IS NULL AND JSONB_EXISTS(constraints, 'maxCount') = FALSE
SQL
        );
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("UPDATE metadata SET constraints = constraints - 'maxCount'");
    }
}
