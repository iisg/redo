<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemResourceKind;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add resource to user.
 */
class Version20170611073853 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        // we assume that database is empty before migrate
        $this->addSql('ALTER TABLE "user" ADD user_data_id INT NULL');
        $this->addSql('ALTER TABLE "user" DROP firstname');
        $this->addSql('ALTER TABLE "user" DROP lastname');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6496FF8BF36 FOREIGN KEY (user_data_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496FF8BF36 ON "user" (user_data_id)');
        $this->addSql('ALTER INDEX idx_54fcd59fa76ed395 RENAME TO IDX_2DE8C6A3A76ED395');
        $this->addSql('ALTER INDEX idx_54fcd59fd60322ac RENAME TO IDX_2DE8C6A3D60322AC');
        $this->addSql('ALTER SEQUENCE "resource_kind_id_seq" RESTART WITH 100');
        $this->createEmptyResourcesForUsers();
        $this->addSql('ALTER TABLE "user" ALTER user_data_id SET NOT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6496FF8BF36');
        $this->addSql('DROP INDEX UNIQ_8D93D6496FF8BF36');
        $this->addSql('ALTER TABLE "user" ADD firstname VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD lastname VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP user_data_id');
        $this->addSql('ALTER INDEX idx_2de8c6a3a76ed395 RENAME TO idx_54fcd59fa76ed395');
        $this->addSql('ALTER INDEX idx_2de8c6a3d60322ac RENAME TO idx_54fcd59fd60322ac');
    }

    private function createEmptyResourcesForUsers() {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id FROM "user"');
        foreach ($results as $userRow) {
            $this->addSql("INSERT INTO resource(id, kind_id, contents) VALUES(nextval('resource_id_seq'), " . SystemResourceKind::USER . ", '[]'::JSONB)");
            $this->addSql("UPDATE \"user\" SET user_data_id=currval('resource_id_seq') WHERE id=$userRow[id]");
        }
    }
}
