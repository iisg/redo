import {RoutableComponentActivate} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {User} from "../user";
import {ContextResourceClass} from './../../resources/context/context-resource-class';

@autoinject
export class UserDetails implements RoutableComponentActivate {
  user: User;

  constructor(private userRepository: UserRepository,
              private contextResourceClass: ContextResourceClass) {
  }

  async activate(params: any) {
    this.contextResourceClass.setCurrent('users');
    this.user = await this.userRepository.get(params.id);

  }

}
