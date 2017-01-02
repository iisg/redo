import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {User} from "../user";
import {metricTime} from "../../common/metrics/metrics-decorators";

@autoinject
export class CurrentUserFetcher {
  static readonly CURRENT_USER_KEY = "current-user";

  constructor(private userRepository: UserRepository) {
  }

  @metricTime("fetching_user")
  fetch(): Promise<User> {
    return this.userRepository.get('current');
  }
}
