import {inject} from "aurelia-dependency-injection";
import {User} from "../user";
import {CurrentUserFetcher} from "./current-user-fetcher";

@inject(CurrentUserFetcher.CURRENT_USER_KEY)
export class CurrentUserLabel {
  currentUser: User;

  constructor(currentUser: User) {
    this.currentUser = currentUser;
  }
}
