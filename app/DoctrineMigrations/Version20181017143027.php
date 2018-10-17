<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Add endpoint_usage_log table
 */
class Version20181017143027 extends RepekaMigration {
    public function migrate() {
        $this->addSql('CREATE SEQUENCE endpoint_usage_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE endpoint_usage_log (id INT NOT NULL, resource_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, client_ip VARCHAR(255) NOT NULL, usage_date_time TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, usage_key VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_3B76444189329D25 ON endpoint_usage_log (resource_id)');
        $this->addSql(
            'ALTER TABLE endpoint_usage_log ADD CONSTRAINT FK_3B76444189329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }
}
