import {CurrentUser} from "./current-user";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class CurrentUserLabel {
  currentUser: CurrentUser;

  constructor(currentUser: CurrentUser) {
    this.currentUser = currentUser;
  }
}
