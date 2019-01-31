import {autoinject} from "aurelia-dependency-injection";
import {Router} from "aurelia-router";
import {FrontendConfig} from "../../../config/FrontendConfig";

@autoinject
export class TopBar {
  userIp: string;
  applicationName: string;

  constructor(private router: Router) {
    this.userIp = FrontendConfig.get('userIp');
    this.applicationName = FrontendConfig.get('application_name');
  }
}
