import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class CurrentUserFetcher {
  static readonly CURRENT_USER_KEY = "current-user";
}
