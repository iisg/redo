<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;

/**
 * Add system metadata VISIBILITY and TEASER_VISIBILITY to all resources
 * and set their values to operators of specific resource class
 */
class Version20190110131008 extends RepekaMigration {
    public function migrate() {
        $users = $this->fetchAll('SELECT id, user_data_id, roles FROM "user"');
        $resourceClassUsersMap = $this->createResourceClassUsersMap($users);
        $visibilityMetadataId = SystemMetadata::VISIBILITY;
        $teaserVisibilityMetadataId = SystemMetadata::TEASER_VISIBILITY;
        $resourceClasses = $this->container->getParameter('repeka.resource_classes');
        foreach ($resourceClasses as $resourceClass) {
            $allowedViewers = implode(', ', array_map(
                function ($id) {
                    return "{\"value\": $id}";
                },
                $resourceClassUsersMap[$resourceClass]
            ));
            $jsonToAdd = "'{\"$visibilityMetadataId\": [$allowedViewers], \"$teaserVisibilityMetadataId\": [$allowedViewers]}'";
            $this->addSql("UPDATE resource SET contents = contents || $jsonToAdd::jsonb WHERE resource_class = '$resourceClass'");
        }
    }

    /**
     * @param $users
     * @return mixed
     */
    private function createResourceClassUsersMap($users) {
        $resourceClassUsersMap = [];
        $resourceClasses = $this->container->getParameter('repeka.resource_classes');
        foreach ($resourceClasses as $resourceClass) {
            $resourceClassUsersMap[$resourceClass] = [];
        }
        foreach ($users as $user) {
            foreach ($resourceClasses as $resourceClass) {
                $roles = json_decode($user['roles'], true);
                if (in_array(SystemRole::OPERATOR()->roleName($resourceClass), $roles)) {
                    $resourceClassUsersMap[$resourceClass][] = $user['user_data_id'];
                }
            }
        }
        return $resourceClassUsersMap;
    }
}
