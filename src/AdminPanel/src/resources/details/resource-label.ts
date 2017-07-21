import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "../resource";

@autoinject
export class ResourceLabelValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(resource: Resource): string {
    const translatedPrefix = this.i18n.tr('Resource');
    return (resource != undefined)
      ? `${translatedPrefix} #${resource.id}`
      : '';
  }
}
