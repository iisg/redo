import {UserRole} from "./roles/user-role";

export class User {
  id: number;
  username: string;
  email: string;
  firstname: string;
  lastname: string;
  roles: Array<UserRole>;

  public get roleIdentifiers() {
    return this.roles.map(role => role.systemRoleIdentifier);
  }
}
