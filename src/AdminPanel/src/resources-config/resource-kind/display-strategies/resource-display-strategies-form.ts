import {computedFrom, signalBindings} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {ResourceKind} from "../resource-kind";
import {DISPLAY_STRATEGIES} from "./display-strategies";
import {SampleResource} from "./sample-resource";
import {ResourceDisplayStrategyEvaluator} from "./resource-display-strategy-evaluator";
import {autoinject} from "aurelia-dependency-injection";
import {ValidationRules} from "aurelia-validation";
import {ResourceDisplayStrategyValueConverter} from "./resource-display-strategy";

@autoinject
export class ResourceDisplayStrategiesForm {
  @bindable resourceKind: ResourceKind;

  readonly displayStrategies: string[] = DISPLAY_STRATEGIES;
  currentlyEditedDisplayStrategy: string = DISPLAY_STRATEGIES[0];

  constructor(private strategyEvaluator: ResourceDisplayStrategyEvaluator) {
  }

  resourceKindChanged() {
    if (this.resourceKind) {
      let rules = undefined;
      for (let strategy of this.displayStrategies) {
        if (!this.resourceKind.displayStrategies[strategy]) {
          this.resourceKind.displayStrategies[strategy] = '';
        }
        rules = (rules || ValidationRules)
          .ensure(strategy)
          .satisfies(value => this.compileTemplate(value))
          .withMessageKey('Template compilation error');
      }
      rules.on(this.resourceKind.displayStrategies);
    }
  }

  @computedFrom("resourceKind", "resourceKind.metadataList.length")
  get sampleResource() {
    return new SampleResource(this.resourceKind);
  }

  compileTemplate(template: string): boolean {
    signalBindings(ResourceDisplayStrategyValueConverter.RESOURCE_DISPLAY_STRATEGIES_UPDATED);
    try {
      this.strategyEvaluator.compileTemplate(template);
      return true;
    } catch ({message}) {
      return false;
    }
  }
}
