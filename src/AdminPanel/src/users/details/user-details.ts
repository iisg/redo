import {RoutableComponentActivate} from "aurelia-router";
import {inject, autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {User} from "../user";
import {CurrentUserFetcher} from "../current/current-user-fetcher";
import {computedFrom} from "aurelia-binding";

@autoinject
@inject(CurrentUserFetcher.CURRENT_USER_KEY)
export class UserDetails implements RoutableComponentActivate {
  user: User;

  constructor(private currentUser: User, private userRepository: UserRepository) {
  }

  async activate(params: any) {
    if (params.id == this.currentUser.id) {
      this.user = this.currentUser;
    } else {
      this.user = await this.userRepository.get(params.id);
    }
  }

  @computedFrom('user')
  get isCurrentUser() {
    return this.user == this.currentUser;
  }
}
