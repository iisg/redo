<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * User roles assignment.
 */
class Version20170404101209 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id UUID NOT NULL, PRIMARY KEY(user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_54FCD59FA76ED395 ON user_role (user_id)');
        $this->addSql('CREATE INDEX IDX_54FCD59FD60322AC ON user_role (role_id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO user_role (user_id, role_id) SELECT "user"."id" "user_id", role.id role_id FROM "user" CROSS JOIN "role" WHERE jsonb_exists("user".static_permissions::jsonb, \'ADMIN_PANEL\');');
        $this->addSql('ALTER TABLE "user" DROP static_permissions');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('ALTER TABLE "user" ADD static_permissions JSON DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".static_permissions IS \'(DC2Type:json)\'');
    }
}
