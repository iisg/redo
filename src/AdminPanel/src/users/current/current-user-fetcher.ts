import {autoinject} from "aurelia-dependency-injection";
import {UserRepository} from "../user-repository";
import {User} from "../user";
import {metricTime} from "../../common/metrics/metrics-decorators";
import {HttpResponseMessage} from "aurelia-http-client";

@autoinject
export class CurrentUserFetcher {
  static readonly CURRENT_USER_KEY = "current-user";

  constructor(private userRepository: UserRepository) {
  }

  @metricTime("fetching_user")
  fetch(): Promise<User> {
    return this.userRepository.get('current').catch((response: HttpResponseMessage) => {
      if (response.statusCode == 403)
        window.location.assign('/403');
    });
  }
}
