<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add code column in language table.
 */
class Version20161129213649 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('DROP SEQUENCE language_id_seq CASCADE');
        $this->addSql('ALTER TABLE language ADD code VARCHAR(5)');
        $this->addSql('ALTER TABLE language DROP id');
        $this->addSql('UPDATE language SET code = flag');
        $this->addSql('ALTER TABLE language ALTER code SET NOT NULL');
        $this->addSql('ALTER TABLE language ADD PRIMARY KEY (code)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE language_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP INDEX language_pkey');
        $this->addSql('ALTER TABLE language ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE language DROP code');
        $this->addSql('ALTER TABLE language ADD PRIMARY KEY (id)');
    }
}
