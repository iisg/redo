<?php declare(strict_types=1);
namespace Repeka\Migrations;

class Version20190612095513 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE endpoint_usage_log RENAME TO event_log');
        $this->addSql('ALTER TABLE event_log RENAME COLUMN usage_key TO event_name');
        $this->addSql('ALTER TABLE event_log RENAME COLUMN usage_date_time TO event_date_time');
        $this->addSql('ALTER SEQUENCE endpoint_usage_log_id_seq RENAME TO event_log_id_seq');
    }
}
