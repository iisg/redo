import {Resource} from "../../../resources/resource";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";

@autoinject
export class ResourceDisplayStrategyValueConverter implements ToViewValueConverter {
  constructor(private i18n: I18N) {
  }

  toView(resource: Resource, displayStrategyId: string): any {
    return resource.displayStrategies[displayStrategyId] || this.i18n.tr('Resource') + ` #${resource.id}`;
  }
}