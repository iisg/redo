import {computedFrom} from "aurelia-binding";
import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {values} from "lodash";
import {WorkflowRepository} from "workflows/workflow-repository";
import {InCurrentLanguageValueConverter} from 'resources-config/multilingual-field/in-current-language';

@autoinject
export class AuditTransitionChooser {
  @bindable(twoWay) selectedTransitionsIds: string[];
  private transitionsLabelsByIds: StringMap<string>;

  constructor(private workflowRepository: WorkflowRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private i18n: I18N) {
  }

  attached() {
    this.workflowRepository.getList().then(values => {
      let transitionsLabelsByIds = {};
      values.forEach(workflow => {
        let currentName = this.inCurrentLanguage.toView(workflow.name);
        workflow.transitions.forEach(transition => {
          transitionsLabelsByIds[transition.id] = `${currentName}: ${this.inCurrentLanguage.toView(transition.label)}`;
        });
      });
      this.transitionsLabelsByIds = transitionsLabelsByIds;
    });
  }

  transitionLabel(transitionId: string) {
    return this.transitionsLabelsByIds[transitionId] || `${this.i18n.tr('Transition')} ${transitionId}`;
  }

  @computedFrom('transitionsLabelsByIds')
  get transitionsIds(): string[] {
    return this.transitionsLabelsByIds && Object.keys(this.transitionsLabelsByIds);
  }

}
