import {Workflow} from "../../workflow";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowPlugin} from "./workflow-plugin";

@autoinject
export class WorkflowPluginsConfiguration {
  @bindable workflow: Workflow;
  @bindable config: StringMap<any>;

  private workflowPlugins: WorkflowPlugin[];

  constructor(private workflowRepository: WorkflowRepository) {
  }

  async workflowChanged() {
    if (this.workflow) {
      this.workflowPlugins = await this.workflowRepository.getPlugins(this.workflow);
      this.configChanged();
    }
  }

  configChanged() {
    for (let plugin of (this.workflowPlugins || [])) {
      if (!this.config[plugin.name]) {
        this.config[plugin.name] = {};
      }
    }
  }
}
