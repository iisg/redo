import {Workflow} from "../workflow";
import {WorkflowRepository} from "../workflow-repository";
import {autoinject} from "aurelia-dependency-injection";
import {RoutableComponentActivate, RouteConfig} from "aurelia-router";
import {I18N} from "aurelia-i18n";
import {InCurrentLanguageValueConverter} from "../../resources-config/multilingual-field/in-current-language";

@autoinject
export class WorkflowDetails implements RoutableComponentActivate {
  workflow: Workflow;

  constructor(private workflowRepository: WorkflowRepository, private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  activate(params: any, routeConfig: RouteConfig): void {
    this.workflowRepository.get(params.id).then(workflow => {
      this.workflow = workflow;
      routeConfig.navModel.setTitle(this.inCurrentLanguage.toView(workflow.name) + ' - ' + this.i18n.tr('Workflows'));
    });
  }
}
