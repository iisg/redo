<?php

namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Removes user password and e-mail requirements
 */
class Version20170505120015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER email DROP NOT NULL');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER email SET NOT NULL');
    }
}
