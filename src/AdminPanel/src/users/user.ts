import {UserRole} from "./roles/user-role";
import {Resource} from "resources/resource";

export class User {
  id: number;
  username: string;
  email: string;
  userData: Resource;
  roles: Array<UserRole>;

  public get roleIdentifiers() {
    return this.roles.map(role => role.systemRoleIdentifier);
  }
}
