import {NavbarSearchProvider} from "./navbar-search";
import {NavigationInstruction} from "aurelia-router";
import {WorkflowRepository} from "../../../../workflows/workflow-repository";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class WorkflowNavbarSearchProvider implements NavbarSearchProvider {
  constructor(private workflowRepository: WorkflowRepository) {
  }

  async getResourceClass(navigationInstruction: NavigationInstruction): Promise<string> {
    if (navigationInstruction.params.hasOwnProperty('resourceClass')) {
      return navigationInstruction.params.resourceClass;
    }
    const workflow = await this.workflowRepository.get(navigationInstruction.params.id);
    return workflow.resourceClass;
  }
}
