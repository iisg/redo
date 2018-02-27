import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {WorkflowRepository} from "../../../workflows/workflow-repository";
import {InCurrentLanguageValueConverter} from "../../../resources-config/multilingual-field/in-current-language";

@autoinject
export class WorkflowBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private workflowRepository: WorkflowRepository,
              private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    let resourceClass: string = navigationInstruction.params.resourceClass;
    const breadcrumbs: BreadcrumbItem[] = [];
    if (resourceClass) {
      breadcrumbs.push({label: this.i18n.tr('New workflow')});
    }
    else {
      const workflow = await this.workflowRepository.get(navigationInstruction.params.id);
      resourceClass = workflow.resourceClass;
      breadcrumbs.push({label: this.inCurrentLanguage.toView(workflow.name)});
    }
    breadcrumbs.unshift({
      label: this.i18n.tr(`resource_classes::${resourceClass}//workflows`),
      route: 'workflows',
      params: {resourceClass}
    });
    return breadcrumbs;
  }
}
