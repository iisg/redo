import {Router} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class NestedNavigationMenu {
  constructor(public router: Router) {
  }
}
