import {Resource} from "../resources/resource";
import {automapped, map} from "common/dto/decorators";

@automapped
export class User {
  static NAME = 'User';

  @map id: number;
  @map username: string;
  @map userData: Resource = new Resource();
  @map('string[]') roles: string[] = [];
  groupsIds: number[] = [];
}
