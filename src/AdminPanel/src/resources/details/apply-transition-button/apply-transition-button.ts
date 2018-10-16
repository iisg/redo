import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {DisabilityReason} from "common/components/buttons/toggle-button";
import {WorkflowTransition} from "workflows/workflow";
import {Resource} from "../../resource";

@autoinject
export class ApplyTransitionButton {
  @bindable resource: Resource;
  @bindable transition: WorkflowTransition;
  @bindable iconName: string;

  constructor(private i18n: I18N) {
  }

  stopIfDisabled(event: Event) {
    if (!this.canApplyTransition) {
      event.stopPropagation();
    }
  }

  @computedFrom("resource", "transition")
  get canApplyTransition(): boolean {
    return this.resource.canApplyTransition(this.transition);
  }

  @computedFrom('resource', 'transition')
  get transitionInactivityReasons(): DisabilityReason[] {
    if (this.resource === undefined || this.transition === undefined) {
      return undefined;
    }
    const reasonCollection = this.resource.blockedTransitions[this.transition.id];
    const reasons: DisabilityReason[] = [];
    if (reasonCollection) {
      if (reasonCollection.otherUserAssigned) {
        reasons.push({icon: 'user-2', message: "Someone else is assigned this action"});
      }
    }
    return reasons;
  }

  reasonTooltip(reason: DisabilityReason) {
    const message = this.i18n.tr(reason.message);
    return (reason.details !== undefined)
      ? `${message} (${reason.details})`
      : message;
  }
}
