import {CurrentUserFetcher} from "../../users/current/current-user-fetcher";
import {inject} from "aurelia-dependency-injection";
import {User} from "../../users/user";

@inject(CurrentUserFetcher.CURRENT_USER_KEY)
export class UserRoleChecker {
  private currentUserRoles: Array<string>;

  constructor(currentUser: User) {
    this.currentUserRoles = currentUser.roles.map(role => role.systemRoleIdentifier);
  }

  public hasAll(desiredRoles: Array<string>): boolean {
    return desiredRoles
      .filter(role => this.currentUserRoles.indexOf(role) == -1)
      .length == 0;
  }
}
