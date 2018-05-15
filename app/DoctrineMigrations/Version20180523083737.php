<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceContents;

/**
 * Invert user group members relationship.
 */
class Version20180523083737 extends RepekaMigration {
    public function migrate() {
        $this->updateUserResourceKinds();
        $userGroupsRkIds = $this->updateUserResources();
        $this->updateGroupMemberAllowedRks($userGroupsRkIds);
    }

    private function updateUserResourceKinds() {
        $userResourceClass = SystemResourceClass::USER;
        $userResourceKinds = $this->fetchAll("SELECT id, metadata_list FROM resource_kind WHERE resource_class = '$userResourceClass'");
        foreach ($userResourceKinds as $userResourceKind) {
            $metadataList = json_decode($userResourceKind['metadata_list'], true);
            if ($userResourceKind['id'] == SystemResourceKind::USER) {
                $metadataList[] = ['id' => SystemMetadata::GROUP_MEMBER];
            } else {
                $metadataList = array_filter(
                    $metadataList,
                    function (array $metadataOverride) {
                        return $metadataOverride['id'] != SystemMetadata::GROUP_MEMBER;
                    }
                );
            }
            $userResourceKind['metadata_list'] = json_encode($metadataList);
            $this->addSql('UPDATE resource_kind SET metadata_list = :metadata_list WHERE id = :id', $userResourceKind);
        }
    }

    private function updateUserResources(): array {
        $userResourceClass = SystemResourceClass::USER;
        $userResourceKindId = SystemResourceKind::USER;
        $userGroups = $this->fetchAll(
            "SELECT id, kind_id, contents FROM resource WHERE resource_class = '$userResourceClass' AND kind_id != $userResourceKindId"
        );
        $userGroupsToUpdate = [];
        $userGroupsRkIds = [];
        foreach ($userGroups as $userGroup) {
            $contents = json_decode($userGroup['contents'], true);
            if (isset($contents[SystemMetadata::GROUP_MEMBER])) {
                foreach ($contents[SystemMetadata::GROUP_MEMBER] as $metadataValue) {
                    $userId = intval($metadataValue['value']);
                    if (!isset($userGroupsToUpdate[$userId])) {
                        $userGroupsToUpdate[$userId] = [];
                    }
                    $userGroupsToUpdate[$userId][] = $userGroup['id'];
                }
                unset($contents[SystemMetadata::GROUP_MEMBER]);
                $userGroup['contents'] = json_encode($contents);
                $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $userGroup);
                $userGroupsRkIds[] = $userGroup['kind_id'];
            }
        }
        foreach ($userGroupsToUpdate as $userId => $userGroupIds) {
            $user = $this->fetchAll("SELECT id, contents FROM resource WHERE id = $userId")[0];
            $user['contents'] = ResourceContents::fromArray(json_decode($user['contents'], true));
            $user['contents'] = $user['contents']->withReplacedValues(SystemMetadata::GROUP_MEMBER, $userGroupIds);
            $user['contents'] = json_encode($user['contents']->toArray());
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $user);
        }
        return array_unique($userGroupsRkIds);
    }

    private function updateGroupMemberAllowedRks(array $userGroupsRkIds) {
        $groupMemberMetadata = $this->fetchAll("SELECT id, constraints FROM metadata WHERE id = " . SystemMetadata::GROUP_MEMBER);
        if ($groupMemberMetadata) {
            $groupMemberMetadata = $groupMemberMetadata[0];
            $groupMemberMetadata['constraints'] = json_decode($groupMemberMetadata['constraints'], true);
            $groupMemberMetadata['constraints']['resourceKind'] = $userGroupsRkIds;
            $groupMemberMetadata['constraints'] = json_encode($groupMemberMetadata['constraints']);
            $this->addSql('UPDATE metadata SET constraints = :constraints WHERE id = :id', $groupMemberMetadata);
        }
    }
}
