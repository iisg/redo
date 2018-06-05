<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add user FK's CASCADING options in order to be able to remove them gracefully.
 */
class Version20180528200825 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT fk_54fcd59fa76ed395');
        $this->addSql(
            'ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) 
             REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('ALTER TABLE audit DROP CONSTRAINT FK_9218FF79A76ED395');
        $this->addSql(
            'ALTER TABLE audit ADD CONSTRAINT FK_9218FF79A76ED395 FOREIGN KEY (user_id) 
             REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
