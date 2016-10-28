import {CurrentUser} from "./current-user";
import {autoinject} from "aurelia-dependency-injection";
import {inlineView} from "aurelia-templating";

@autoinject
@inlineView("<template>${currentUser.username}</template>")
export class CurrentUserLabel {
  currentUser: CurrentUser;

  constructor(currentUser: CurrentUser) {
    this.currentUser = currentUser;
  }
}
