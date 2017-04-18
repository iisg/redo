import {UserRepository} from "./user-repository";
import {User} from "./user";
import {ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class UserList implements ComponentAttached {
  userList: User[];

  constructor(private userRepository: UserRepository) {
  }

  async attached() {
    this.userList = await this.userRepository.getList();
  }
}
