import {bindable} from "aurelia-templating";
import {User} from "../user";
import {inject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {CurrentUserFetcher} from "../current/current-user-fetcher";
import {Resource} from "../../resources/resource";

@inject(UserRepository, CurrentUserFetcher.CURRENT_USER_KEY)
export class UserGroups {
  @bindable userData: Resource;

  user: User;
  private userGroups: Resource[];

  constructor(private userRepository: UserRepository) {
  }

  async userDataChanged() {
    this.user = await this.userRepository.getRelatedUser(this.userData);
    this.userGroups = await this.userRepository.getGroups(this.user);
  }
}
