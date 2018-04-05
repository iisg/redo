<?php declare(strict_types = 1);

namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Erase every maxCount=0 constraint!
 */
class Version20180424170831 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql("UPDATE metadata SET constraints = constraints - 'maxCount' where constraints -> 'maxCount' = '0';");
    }

    public function down(Schema $schema) {
    }
}
