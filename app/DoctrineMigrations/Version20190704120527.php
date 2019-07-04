<?php declare(strict_types=1);
namespace Repeka\Migrations;

class Version20190704120527 extends RepekaMigration {
    public function migrate() {
        $this->addSql("ALTER TABLE resource ADD pending_updates jsonb DEFAULT '[]' NOT NULL");
        $this->addSql("COMMENT ON COLUMN resource.pending_updates IS '(DC2Type:jsonb)'");
    }
}
