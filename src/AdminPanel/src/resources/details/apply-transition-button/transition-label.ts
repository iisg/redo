import {WorkflowTransition} from "../../../workflows/workflow";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {InCurrentLanguageValueConverter} from "../../../resources-config/multilingual-field/in-current-language";

@autoinject
export class TransitionLabelValueConverter implements ToViewValueConverter {
  public constructor(private i18n: I18N, private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(transition: WorkflowTransition): any {
    if (transition.id == 'update') {
      return this.i18n.tr('Edit');
    } else {
      const label = this.inCurrentLanguage.toView(transition.label);
      return label || this.i18n.tr('Transition');
    }
  }
}
