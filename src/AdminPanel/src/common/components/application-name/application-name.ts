import {autoinject} from "aurelia-dependency-injection";
import {inlineView} from "aurelia-templating";
import {I18nParams} from "config/i18n";

@inlineView('<template>${applicationName}</template>')
@autoinject
export class ApplicationName {
  applicationName: string;

  constructor(i18nParams: I18nParams) {
    this.applicationName = i18nParams.applicationName;
  }
}
