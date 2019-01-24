import {NavigationInstruction} from "aurelia-router";
import {autoinject} from "aurelia-dependency-injection";
import {BreadcrumbItem, BreadcrumbsProvider} from "./breadcrumbs";
import {I18N} from "aurelia-i18n";
import {WorkflowRepository} from "../../../workflows/workflow-repository";
import {InCurrentLanguageValueConverter} from "../../../resources-config/multilingual-field/in-current-language";
import {ResourceClassTranslationValueConverter} from "../../value-converters/resource-class-translation-value-converter";

@autoinject
export class WorkflowBreadcrumbsProvider implements BreadcrumbsProvider {
  constructor(private workflowRepository: WorkflowRepository,
              private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private resourceClassTranslationValueConverter: ResourceClassTranslationValueConverter) {
  }

  async getBreadcrumbs(navigationInstruction: NavigationInstruction): Promise<BreadcrumbItem[]> {
    let resourceClass: string = navigationInstruction.params.resourceClass;
    const breadcrumbs: BreadcrumbItem[] = [];
    if (resourceClass) {
      breadcrumbs.push({label: this.resourceClassTranslationValueConverter.toView('New workflow', resourceClass)});
    } else {
      const workflow = await this.workflowRepository.get(navigationInstruction.params.id);
      resourceClass = workflow.resourceClass;
      breadcrumbs.push({label: this.inCurrentLanguage.toView(workflow.name)});
    }
    breadcrumbs.unshift({
      label: this.resourceClassTranslationValueConverter.toView('workflows', resourceClass),
      route: 'workflows',
      params: {resourceClass}
    });
    return breadcrumbs;
  }
}
