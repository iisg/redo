import {Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class Sidebar {
  readonly router: Router;

  constructor(router: Router) {
    this.router = router;
  }
}
