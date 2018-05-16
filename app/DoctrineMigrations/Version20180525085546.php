<?php declare(strict_types = 1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemTransition;

/**
 * Remove records from audit containing system transitions
 */
class Version20180525085546 extends AbstractMigration {
    public function up(Schema $schema) {
        $systemTransitions = array_values(SystemTransition::toArray());
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('DELETE FROM audit WHERE commandname = :resourceTransition AND data->>\'transitionId\' in (\''.implode("','", $systemTransitions).'\')',
            ['resourceTransition' => 'resource_transition']);
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }
}
