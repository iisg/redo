import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "../../resources/resource";
import {ResourceRepository} from "../../resources/resource-repository";
import {SystemMetadata} from "../../resources-config/metadata/system-metadata";

@autoinject
export class UsersInGroup {
  @bindable userGroup: Resource;
  private users: Resource[];

  constructor(private resourceRepository: ResourceRepository) {
  }

  async userGroupChanged() {
    const groupsFilter = {};
    groupsFilter[SystemMetadata.GROUP_MEMBER.id] = this.userGroup.id;
    this.users = await this.resourceRepository.getListQuery()
      .filterByResourceClasses('users')
      .filterByContents(groupsFilter)
      .get();
  }

  get briefMetadata() {
    if (this.users && this.users.length) {
      return this.users[0].kind.metadataList.filter(m => m.shownInBrief);
    }
  }
}
