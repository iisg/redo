import {bindable} from "aurelia-templating";
import {Resource} from "../resource";
import {WorkflowTransition} from "../../workflows/workflow";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {InCurrentLanguageValueConverter} from "../../resources-config/multilingual-field/in-current-language";

@autoinject
export class TransitionButton {
  @bindable resource: Resource;
  @bindable transition: WorkflowTransition;
  @bindable applyTransition: () => any;
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
  get transitionInactiveReason(): string {
    const explanation = this.resource.getUnsatisfiedTransitionExplanation(this.transition);
    let reasons = [];
    if (explanation.userMissingRequiredRole) {
      reasons.push(this.i18n.tr("You don't have required role"));
    }
    for (const metadataId of explanation.missingMetadataIds) {
      const label = this.getMetadataLabelForBaseId(metadataId);
      reasons.push(this.i18n.tr('Missing metadata:') + ' ' + label);
    }
    return reasons.join('\n');
  }

  private getMetadataLabelForBaseId(baseMetadataId: number): string {
    for (const metadata of this.resource.kind.metadataList) {
      if (metadata.baseId == baseMetadataId) {
        return this.inCurrentLanguage.toView(metadata.label);
      }
    }
  }
}
