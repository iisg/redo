import {bindable} from "aurelia-templating";
import {ResourceKind} from "../resource-kind";
import {DISPLAY_STRATEGIES} from "./display-strategies";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceDisplayStrategiesForm {
  @bindable resourceKind: ResourceKind;

  readonly displayStrategies: string[] = DISPLAY_STRATEGIES;
  currentlyEditedDisplayStrategy: string = DISPLAY_STRATEGIES[0];

  resourceKindChanged() {
    if (this.resourceKind) {
      for (let strategy of this.displayStrategies) {
        if (!this.resourceKind.displayStrategies[strategy]) {
          this.resourceKind.displayStrategies[strategy] = '';
        }
      }
    }
  }
}
