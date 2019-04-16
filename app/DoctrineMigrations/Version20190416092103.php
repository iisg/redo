<?php declare(strict_types=1);
namespace Repeka\Migrations;

class Version20190416092103 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE endpoint_usage_log DROP CONSTRAINT FK_3B76444189329D25');
        $this->addSql(
            'ALTER TABLE endpoint_usage_log ADD CONSTRAINT FK_3B76444189329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
