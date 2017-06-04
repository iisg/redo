import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {WorkflowTransition} from "../../workflows/workflow";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";

@autoinject
export class TransitionButton {
  @bindable resource: Resource;
  @bindable transition: WorkflowTransition;
  @bindable applyTransition: () => any;
  @bindable fallbackText: string;

  constructor(private i18n: I18N) {
  }

  @computedFrom('resource', 'transition')
  get canApplyTransition(): boolean {
    if (!this.resource || !this.transition) {
      return false;
    }
    return this.resource.canApplyTransition(this.transition);
  }
}
