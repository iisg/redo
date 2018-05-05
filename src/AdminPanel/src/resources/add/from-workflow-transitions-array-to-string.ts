import {autoinject} from "aurelia-dependency-injection";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {WorkflowTransition} from "workflows/workflow";

@autoinject
export class FromWorkflowTransitionsArrayToStringValueConverter implements ToViewValueConverter {
  constructor (private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  toView(workflowTransitions: WorkflowTransition[]): string {
    return workflowTransitions
      ? '- ' + workflowTransitions.map(workflowTransition => this.inCurrentLanguage.toView(workflowTransition.label)).join('\n-')
      : '';
  }
}
