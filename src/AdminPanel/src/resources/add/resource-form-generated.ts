import {bindable} from "aurelia-templating";
import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {Resource} from "resources/resource";
import {bindingMode} from "aurelia-binding";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceFormGenerated {
  @bindable
  resourceKind: ResourceKind;

  @bindable({defaultBindingMode: bindingMode.twoWay})
  resource: Resource;

  currentLanguageCode: string;

  resourceKindChanged() {
    if (!this.resourceKind.id) this.resource.contents = {};
  }

  constructor(i18n: I18N) {
    this.currentLanguageCode = i18n.getLocale().toUpperCase();
  }
}
