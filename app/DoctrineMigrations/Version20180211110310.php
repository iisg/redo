<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Fix shown_in_brief nulls.
 */
class Version20180211110310 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('UPDATE metadata m1 SET shown_in_brief = (SELECT shown_in_brief FROM metadata m2 WHERE m2.id = m1.base_id) WHERE shown_in_brief IS NULL');
        $this->addSql('ALTER TABLE metadata ALTER shown_in_brief SET NOT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }
}
