import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";

@autoinject()
export class About {
  version: String;

  constructor(config: Configure) {
    this.version = config.get('version');
  }
}
