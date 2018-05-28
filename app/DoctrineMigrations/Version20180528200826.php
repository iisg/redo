<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceContents;

/**
 * Deduplicate users and unify their usernames after introducing case-insensitive usernames.
 */
class Version20180528200826 extends RepekaMigration {
    public function migrate() {
        $usersToDeduplicate = [];
        $users = $this->fetchAll(
            'SELECT resource.id id, contents, password FROM resource 
             INNER JOIN "user" ON user_data_id=resource.id WHERE kind_id = ' . SystemResourceKind::USER
        );
        foreach ($users as $user) {
            if (!$user['password']) { // take action only for PK-Authenticated users
                $user['contents'] = json_decode($user['contents'], true);
                $username = ResourceContents::fromArray($user['contents'])->getValues(SystemMetadata::USERNAME)[0];
                $unifiedUsername = preg_replace('#[^\d]#', '', strtolower($username));
                $usersToDeduplicate[$unifiedUsername][] = $user;
            }
        }
        foreach ($usersToDeduplicate as $unifiedUsername => $users) {
            $user = array_shift($users);
            $user['contents'] = ResourceContents::fromArray($user['contents'])
                ->withReplacedValues(SystemMetadata::USERNAME, [$unifiedUsername])
                ->toArray();
            $user['contents'] = json_encode($user['contents']);
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $user);
            foreach ($users as $user) {
                $this->addSql('DELETE FROM resource WHERE id = :id', $user);
            }
        }
    }
}
