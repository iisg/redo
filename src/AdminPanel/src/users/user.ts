import {UserRole} from "./roles/user-role";
import {Resource} from "../resources/resource";
import {automapped, map} from "common/dto/decorators";

@automapped
export class User {
  static NAME = 'User';

  @map id: number;
  @map username: string;
  @map email: string;
  @map userData: Resource = new Resource();
  @map('UserRole') roles: UserRole[] = [];

  public get roleIdentifiers() {
    return this.roles.map(role => role.systemRoleIdentifier);
  }
}
