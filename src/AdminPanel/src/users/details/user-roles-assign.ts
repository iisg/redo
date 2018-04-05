import {bindable} from "aurelia-templating";
import {User} from "../user";
import {inject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {computedFrom} from "aurelia-binding";
import {CurrentUserFetcher} from "../current/current-user-fetcher";
import {Resource} from "../../resources/resource";

@inject(UserRepository, CurrentUserFetcher.CURRENT_USER_KEY)
export class UserRolesAssign {
  @bindable userData: Resource;

  rolesChanged = false;

  userRoleIds: Array<string> = [];
  user: User;

  constructor(private userRepository: UserRepository, private currentUser: User) {
  }

  async userDataChanged() {
    this.user = await this.userRepository.getRelatedUser(this.userData);
    this.userRoleIds = this.user.roles.map(r => r.id);
  }

  save(): Promise<any> {
    return this.userRepository.updateRoles(this.user, this.userRoleIds).then(user => {
      this.user = user;
    });
  }

  @computedFrom('user')
  get isCurrentUser() {
    return this.user && this.user.id === this.currentUser.id;
  }
}
