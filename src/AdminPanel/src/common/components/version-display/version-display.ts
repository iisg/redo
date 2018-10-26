import {autoinject} from "aurelia-dependency-injection";
import {inlineView} from "aurelia-templating";
import {FrontendConfig} from "../../../config/FrontendConfig";

@inlineView('<template>${version}</template>')
@autoinject()
export class VersionDisplay {
  readonly version: String;

  constructor() {
    this.version = FrontendConfig.get('application_version');
  }
}
