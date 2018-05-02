import {Resource} from "../../../resources/resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceDisplayStrategyValueConverter implements ToViewValueConverter {
  toView(resource: Resource, displayStrategyId: string): any {
    return resource.displayStrategies[displayStrategyId] || '';
  }
}
