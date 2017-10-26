import {RoutableComponentActivate} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {User} from "../user";

@autoinject
export class UserDetails implements RoutableComponentActivate {
  user: User;

  constructor(private userRepository: UserRepository) {
  }

  async activate(params: any) {
    this.user = await this.userRepository.get(params.id);
  }
}
