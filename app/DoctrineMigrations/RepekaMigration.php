<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class RepekaMigration extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public final function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->migrate();
    }

    abstract public function migrate();

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back.');
    }
}
