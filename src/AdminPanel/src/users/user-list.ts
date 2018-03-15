import {UserRepository} from "./user-repository";
import {User} from "./user";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../resources-config/metadata/metadata";
import {getMergedBriefMetadata} from "../common/utils/metadata-utils";
import {ContextResourceClass} from './../resources/context/context-resource-class';

@autoinject
export class UserList implements ComponentAttached {
  userList: User[];
  briefMetadata: Metadata[];

  constructor(private userRepository: UserRepository,
              private contextResourceClass: ContextResourceClass) {
  }

  attached() {
    this.userRepository.getList().then(userList => {
      this.userList = userList;
      this.briefMetadata = this.getMetadataList();
    });
  }

  activate(params: any) {
    this.contextResourceClass.setCurrent('users');
  }
  private getMetadataList(): Metadata[] {
    const resourceKindList = this.userList.map(user => user.userData.kind);
    return getMergedBriefMetadata(resourceKindList);
  }
}
