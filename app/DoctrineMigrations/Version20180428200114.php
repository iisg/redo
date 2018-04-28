<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Clear invalid resource markings. For some reason, the 0.5.0 version sometimes wrote 'null' string into marking column. It caused errors
 * when filtering resources by places.
 */
class Version20180428200114 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql('UPDATE resource SET marking = NULL WHERE jsonb_typeof(marking) <> \'object\';');
    }

    public function down(Schema $schema) {
    }
}
