<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemUserRole;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Remove UUID identifier for roles
 */
class Version20170717221928 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;
    private const ADMIN = '11d87f9d-dd56-4ab1-afe8-9d560a8eaa9d';
    private const OPERATOR = 'c4bde879-afaf-4500-ba43-97451932c964';
    private const USER = 1;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->migrateRolesUUIDtoID();
        $this->updateResourceKindIds($this::USER);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->migrateRolesIDtoUUID();
        $this->updateResourceKindIds(SystemResourceKind::USER);
    }

    private function migrateRolesUUIDtoID() {
        // prepare column in table 'role'
        $this->addSql('ALTER TABLE role ADD tmp_id INT DEFAULT nextval(\'role_id_seq\');');
        $this->addSql('ALTER TABLE role ALTER tmp_id SET NOT NULL');
        // prepare column in table 'user_role'
        $this->addSql('ALTER TABLE user_role ADD tmp_role_id INT');
        $this->addSql('ALTER TABLE user_role ALTER tmp_role_id DROP DEFAULT');
        // migrating
        $this->addSql('UPDATE role SET tmp_id = :systemUserRoleId WHERE id = :systemUserRoleUUID',
            ['systemUserRoleId' => SystemUserRole::ADMIN, 'systemUserRoleUUID' => $this::ADMIN]);
        $this->addSql('UPDATE role SET tmp_id = :systemUserRoleId WHERE id = :systemUserRoleUUID',
            ['systemUserRoleId' => SystemUserRole::OPERATOR, 'systemUserRoleUUID' => $this::OPERATOR]);
        $this->addSql('UPDATE user_role SET tmp_role_id = role.tmp_id FROM role WHERE role.id = user_role.role_id');
        $this->addSql('UPDATE role SET tmp_id = :systemUserRoleId WHERE id = :systemUserRoleUUID',
            ['systemUserRoleId' => SystemUserRole::ADMIN, 'systemUserRoleUUID' => $this::ADMIN]);
        // update table 'user_role'
        $this->addSql('ALTER TABLE user_role DROP role_id');
        $this->addSql('ALTER TABLE user_role RENAME COLUMN tmp_role_id TO role_id');
        // update table 'role'
        $this->addSql('ALTER TABLE role DROP CONSTRAINT role_pkey');
        $this->addSql('ALTER TABLE role DROP id');
        $this->addSql('ALTER TABLE role RENAME COLUMN tmp_id TO id');
        $this->addSql('ALTER TABLE role ADD PRIMARY KEY (id)');
        // update foreign key in user_role
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT role_fk FOREIGN KEY(role_id) REFERENCES role(id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function migrateRolesIDtoUUID() {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        // prepare column in table 'role'
        $this->addSql('ALTER TABLE role ADD tmp_id UUID');
        $this->addSql('ALTER TABLE role ALTER tmp_id SET NOT NULL');
        // prepare column in table 'user_role'
        $this->addSql('ALTER TABLE user_role ADD tmp_role_id UUID');
        $this->addSql('ALTER TABLE user_role ALTER tmp_role_id DROP DEFAULT');
        // migrating
        $this->addSql('UPDATE role SET tmp_id = :systemUserRoleUUID WHERE id = :systemUserRoleId',
            ['systemUserRoleId' => SystemUserRole::ADMIN, 'systemUserRoleUUID' => $this::ADMIN]);
        $this->addSql('UPDATE role SET tmp_id = :systemUserRoleId WHERE id = :systemUserRoleUUID',
            ['systemUserRoleId' => SystemUserRole::OPERATOR, 'systemUserRoleUUID' => $this::OPERATOR]);
        $this->addSql('UPDATE user_role SET tmp_role_id = role.tmp_id FROM role WHERE role.id = user_role.role_id');
        // update table 'user_role'
        $this->addSql('ALTER TABLE user_role DROP role_id');
        $this->addSql('ALTER TABLE user_role RENAME COLUMN tmp_role_id TO role_id');
        // update table 'role'
        $this->addSql('ALTER TABLE role DROP CONSTRAINT role_pkey');
        $this->addSql('ALTER TABLE role DROP id');
        $this->addSql('ALTER TABLE role RENAME COLUMN tmp_id TO id');
        $this->addSql('ALTER TABLE role ADD PRIMARY KEY (id)');
        // update fereign key in user_role
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT role_fk FOREIGN KEY(role_id) REFERENCES role(id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function updateResourceKindIds(int $prevResourceKindId) {
        $this->addSql('ALTER TABLE resource DROP CONSTRAINT FK_BC91F41630602CA9');
        $this->addSql('UPDATE resource_kind SET id = :systemResourceKind WHERE id = :prevResourceKindId',
            ['systemResourceKind' => SystemResourceKind::USER, 'prevResourceKindId' => $prevResourceKindId]);
        $this->addSql('UPDATE resource SET kind_id = :systemResourceKind WHERE kind_id = :prevResourceKindId',
            ['systemResourceKind' => SystemResourceKind::USER, 'prevResourceKindId' => $prevResourceKindId]);
        $this->addSql('UPDATE metadata SET resource_kind_id = :systemResourceKind WHERE resource_kind_id = :prevResourceKindId',
            ['systemResourceKind' => SystemResourceKind::USER, 'prevResourceKindId' => $prevResourceKindId]);
        // revert foreign keys
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F41630602CA9 FOREIGN KEY(kind_id) REFERENCES resource_kind(id)');
    }
}
