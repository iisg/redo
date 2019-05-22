import {inject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {FrontendConfig} from "../../../config/FrontendConfig";
import {CurrentUserFetcher} from "../../../users/current/current-user-fetcher";
import {User} from "../../../users/user";

@inject(Router, CurrentUserFetcher.CURRENT_USER_KEY)
export class TopBar {
  userIp: string;
  applicationName: string;

  constructor(private router: Router, private currentUser: User) {
    this.userIp = FrontendConfig.get('userIp');
    this.applicationName = FrontendConfig.get('application_name');
  }
}
