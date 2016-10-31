import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";

@autoinject()
export class VersionDisplay {
  readonly version: String;

  constructor(config: Configure) {
    this.version = config.get('application_version');
  }
}
