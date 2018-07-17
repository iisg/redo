<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Update data fields in audit, to store resources both before and after edit or transition.
 * data: { resource: {...} }
 * becomes
 * data: { after: { resource: {...} } }
 */
class Version20180718100453 extends RepekaMigration {
    public function migrate() {
        $paramsArray = [
            ['command_name' => 'resource_create', 'put_into' => 'after'],
            ['command_name' => 'resource_delete', 'put_into' => 'before'],
            ['command_name' => 'resource_god_update', 'put_into' => 'before'],
            ['command_name' => 'resource_transition', 'put_into' => 'before'],
            ['command_name' => 'resource_update_contents', 'put_into' => 'before'],
        ];
        // in 'audit' table in 'data' column:
        // - remove JSON object under 'resource' key in selected entries
        // - add removed object again under 'after->resource' or 'before->resource' key
        foreach ($paramsArray as $params) {
            $this->addSql(
                'UPDATE audit SET data = data - \'resource\' || jsonb_build_object(:put_into::text, jsonb_build_object(\'resource\', data->\'resource\')) WHERE commandname = :command_name::character varying(64);',
                $params
            );
        }
    }
}
