<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Event groups.
 */
class Version20190612105311 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE event_log ADD event_group VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_log ALTER url DROP NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER client_ip DROP NOT NULL');
        $this->addSql('ALTER TABLE event_log ALTER event_date_time TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE event_log ALTER event_date_time SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER INDEX idx_3b76444189329d25 RENAME TO IDX_9EF0AD1689329D25');
        $this->addSql('CREATE INDEX event_log_event_date_time_idx ON event_log (event_date_time)');
        $this->addSql('CREATE INDEX event_log_event_name_idx ON event_log (event_name)');
        $this->addSql('CREATE INDEX event_log_event_group_idx ON event_log (event_group)');
        $this->addSql(
            "UPDATE event_log SET event_group='endpoint' WHERE event_name IN('home', 'deposit', 'resourceDetails', 'rss')"
        );
        $this->addSql("UPDATE event_log SET event_group='cite' WHERE event_name IN('endnote', 'iso-690', 'bibtex')");
        $this->addSql("UPDATE event_log SET event_group='download' WHERE event_name IN('resourceDownload', 'resourceBrowse')");
        $this->addSql("UPDATE event_log SET event_group='default' WHERE event_group IS NULL");
        $this->addSql('ALTER TABLE event_log ALTER event_group SET NOT NULL');
        $this->addSql('ALTER TABLE event_log DROP CONSTRAINT fk_3b76444189329d25');
        $this->addSql(
            'ALTER TABLE event_log ADD CONSTRAINT FK_9EF0AD1689329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
