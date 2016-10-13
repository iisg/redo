import {Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";

@autoinject
export class Sidebar implements ComponentAttached {
  readonly router: Router;

  constructor(router: Router) {
    this.router = router;
  }

  attached(): void {
    $(".button-collapse").sideNav();
  }
}
