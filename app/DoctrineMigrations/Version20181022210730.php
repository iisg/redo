<?php
namespace Repeka\Migrations;

/**
 * Add displayStrategyDependencies to resource.
 */
class Version20181022210730 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE resource ADD display_strategy_dependencies jsonb DEFAULT \'{}\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN resource.display_strategy_dependencies IS \'(DC2Type:jsonb)\'');
        $this->addSql('ALTER TABLE endpoint_usage_log ALTER usage_date_time TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE endpoint_usage_log ALTER usage_date_time SET DEFAULT CURRENT_TIMESTAMP');
    }
}
