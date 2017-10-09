<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Drop user username and email columns.
 */
class Version20171009081818 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $users = $this->getUsers();
        foreach ($users as $user) {
            $user->moveUsernameToUserData();
            $this->queueUserDataUpdate($user);
        }
        $this->addSql('DROP INDEX uniq_8d93d649f85e0677');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74');
        $this->addSql('ALTER TABLE "user" DROP username');
        $this->addSql('ALTER TABLE "user" DROP email');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'Down migration not implemented.');
    }

    /** @return Version20171009081818User[] */
    private function getUsers(): array {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection
            ->fetchAll('SELECT u.id, u.username, u.user_data_id, r.contents FROM "user" u INNER JOIN resource r ON u.user_data_id = r.id');
        return array_map(function ($row) {
            return new Version20171009081818User($row['id'], $row['username'], $row['user_data_id'], $row['contents']);
        }, $results);
    }

    private function queueUserDataUpdate(Version20171009081818User $user): void {
        $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $user->getDataAsParams());
    }
}

class Version20171009081818User {
    private $userId;
    private $username;
    private $userDataId;
    private $userDataContents;

    public function __construct($userId, $username, $userDataId, $userDataContents) {
        $this->userId = $userId;
        $this->username = $username;
        $this->userDataId = $userDataId;
        $this->userDataContents = json_decode($userDataContents, true);
    }

    public function moveUsernameToUserData() {
        $this->userDataContents[SystemMetadata::USERNAME] = [$this->username];
    }

    public function getDataAsParams(): array {
        return [
            'id' => $this->userDataId,
            'contents' => json_encode($this->userDataContents),
        ];
    }
}
