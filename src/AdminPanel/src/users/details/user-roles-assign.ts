import {bindable} from "aurelia-templating";
import {User} from "../user";
import {inject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {computedFrom} from "aurelia-binding";
import {CurrentUserFetcher} from "../current/current-user-fetcher";

@inject(UserRepository, CurrentUserFetcher.CURRENT_USER_KEY)
export class UserRolesAssign {
  @bindable user: User;

  rolesChanged = false;

  userRoleIds: Array<string> = [];

  constructor(private userRepository: UserRepository, private currentUser: User) {
  }

  userChanged() {
    this.userRoleIds = this.user.roles.map(r => r.id);
  }

  save(): Promise<any> {
    return this.userRepository.updateRoles(this.user, this.userRoleIds).then(user => {
      this.user = user;
    });
  }

  @computedFrom('user')
  get isCurrentUser() {
    return this.user.id === this.currentUser.id;
  }
}
