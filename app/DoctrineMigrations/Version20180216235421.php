<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix missing resource class for submetadata with parents.
 */
class Version20180216235421 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('UPDATE metadata m1 SET resource_class = (SELECT resource_class FROM metadata m2 WHERE m2.id = m1.parent_id) WHERE parent_id IS NOT NULL');
    }

    public function down(Schema $schema) {
        throw new \RuntimeException('There is no way back.');
    }
}
