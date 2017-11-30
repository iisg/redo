import {bindable} from "aurelia-templating";
import {WorkflowTransition} from "workflows/workflow";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {Resource} from "../../resource";

@autoinject
export class ResourceEditButton {
  @bindable resource: Resource;
  @bindable transition: WorkflowTransition;

  constructor(private i18n: I18N) {
  }

  @computedFrom('resource', 'transition')
  get canEditResource(): boolean {
    if (!this.resource || !this.transition) {
      return false;
    }
    return this.resource.canApplyTransition(this.transition);
  }

  @computedFrom('resource', 'transition')
  get transitionInactiveReasons(): TransitionInactiveReason[] {
    if (this.resource === undefined || this.transition === undefined) {
      return undefined;
    }
    const reasonCollection = this.resource.blockedTransitions[this.transition.id];
    const reasons: TransitionInactiveReason[] = [];
    if (reasonCollection) {
      if (reasonCollection.userMissingRequiredRole) {
        reasons.push({icon: 'user-circle', message: "You don't have required role"});
      }
      if (reasonCollection.otherUserAssigned) {
        reasons.push({icon: 'user-circle', message: "Someone else is assigned this action"});
      }
    }
    return reasons;
  }

  reasonTooltip(reason: TransitionInactiveReason) {
    const message = this.i18n.tr(reason.message);
    return (reason.detail !== undefined)
      ? `${message} (${reason.detail})`
      : message;
  }
}

interface TransitionInactiveReason {
  icon: string;
  message: string;
  detail?: string;
}
