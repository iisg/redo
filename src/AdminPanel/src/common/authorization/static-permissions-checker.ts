import {CurrentUserFetcher} from "../../users/current/current-user-fetcher";
import {inject} from "aurelia-dependency-injection";
import {User} from "../../users/user";

@inject(CurrentUserFetcher.CURRENT_USER_KEY)
export class StaticPermissionsChecker {
  constructor(private currentUser: User) {
  }

  public allAllowed(permissions: Array<string>): boolean {
    return this.missingPermissionsCount(permissions) == 0;
  }

  private missingPermissionsCount(permissions: Array<string>): number {
    return permissions.filter(requiredPermission => this.currentUser.staticPermissions.indexOf(requiredPermission) == -1).length;
  }
}