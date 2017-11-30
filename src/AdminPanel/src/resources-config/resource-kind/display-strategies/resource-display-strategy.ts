import {Resource} from "../../../resources/resource";
import {ResourceDisplayStrategyEvaluator} from "./resource-display-strategy-evaluator";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceDisplayStrategyValueConverter implements ToViewValueConverter {
  public static readonly RESOURCE_DISPLAY_STRATEGIES_UPDATED = 'resource-display-strategies-updated';

  signals = [ResourceDisplayStrategyValueConverter.RESOURCE_DISPLAY_STRATEGIES_UPDATED];

  constructor(private compiler: ResourceDisplayStrategyEvaluator) {
  }

  toView(resource: Resource, displayStrategyId: string): any {
    return this.compiler.getDisplayValue(resource, displayStrategyId);
  }
}
