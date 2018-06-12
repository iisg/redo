<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * User granted roles in database.
 */
class Version20180612202801 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE "user" ADD roles jsonb DEFAULT \'[]\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:jsonb)\'');
    }
}
