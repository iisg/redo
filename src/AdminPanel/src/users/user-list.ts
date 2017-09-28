import {UserRepository} from "./user-repository";
import {User} from "./user";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../resources-config/metadata/metadata";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";

@autoinject
export class UserList implements ComponentAttached {
  userList: User[];
  briefMetadata: Metadata[];

  constructor(private userRepository: UserRepository) {
  }

  attached() {
    this.userRepository.getList().then(userList => {
      this.userList = userList;
      this.briefMetadata = this.getMetadataList();
    });
  }

  private getMetadataList(): Metadata[] {
    const resourceKindList = this.userList.map(user => user.userData.kind);
    return getMergedBriefMetadata(resourceKindList);
  }
}
