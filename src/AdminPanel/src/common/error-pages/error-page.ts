import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {bindable} from "aurelia-templating";

@autoinject
export class ErrorPage {
  @bindable title: string;
  @bindable subtitle: string;
  @bindable message: string;

  constructor(private router: Router) {
  }
}
