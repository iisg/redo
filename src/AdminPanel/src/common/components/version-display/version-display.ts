import {Configure} from "aurelia-configuration";
import {autoinject} from "aurelia-dependency-injection";
import {inlineView} from "aurelia-templating";

@inlineView('<template>${version}</template>')
@autoinject()
export class VersionDisplay {
  readonly version: String;

  constructor(config: Configure) {
    this.version = config.get('application_version');
  }
}
