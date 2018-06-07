<?php
namespace Repeka\Migrations;

/**
 * Get rid of user roles.
 */
class Version20180607104015 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT role_fk');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE role');
    }
}
