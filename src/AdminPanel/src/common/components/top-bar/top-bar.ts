import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {ComponentAttached} from "aurelia-templating";

@autoinject
export class TopBar implements ComponentAttached {
  userIp: string;

  constructor(private router: Router) {
  }

  attached() {
    try {
      this.userIp = localStorage.getItem('user_ip');
    } catch (e) {
      this.userIp = '';
    }
  }
}
