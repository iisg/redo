import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {WorkflowTransition} from "workflows/workflow";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {VoidFunction} from "common/utils/function-utils";

@autoinject
export class TransitionButton {
  @bindable resource: Resource;
  @bindable transition: WorkflowTransition;
  @bindable applyTransition: VoidFunction;
  @bindable fallbackText: string;

  constructor(private i18n: I18N, private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  @computedFrom('resource', 'transition')
  get canApplyTransition(): boolean {
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
    if (reasonCollection.userMissingRequiredRole) {
      reasons.push({icon: 'user-circle', message: "You don't have required role"});
    }
    if (reasonCollection.otherUserAssigned) {
      reasons.push({icon: 'user-circle', message: "Someone else is assigned this action"});
    }
    for (const metadataId of reasonCollection.missingMetadataIds) {
      reasons.push({
        icon: 'times-circle',
        message: 'Missing metadata',
        detail: this.getMetadataLabelForBaseId(metadataId),
      });
    }
    return reasons;
  }

  private getMetadataLabelForBaseId(baseMetadataId: number): string {
    for (const metadata of this.resource.kind.metadataList) {
      if (metadata.baseId == baseMetadataId) {
        return this.inCurrentLanguage.toView(metadata.label);
      }
    }
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
