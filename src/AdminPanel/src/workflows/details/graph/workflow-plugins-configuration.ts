import {Workflow, WorkflowPlacePluginConfiguration} from "../../workflow";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowPlugin} from "./workflow-plugin";

@autoinject
export class WorkflowPluginsConfiguration {
  @bindable workflow: Workflow;
  @bindable pluginsConfig: WorkflowPlacePluginConfiguration[];

  private availableWorkflowPlugins: StringMap<WorkflowPlugin>;

  constructor(private workflowRepository: WorkflowRepository) {
  }

  async workflowChanged() {
    if (this.workflow) {
      const workflowPlugins = await this.workflowRepository.getPlugins(this.workflow);
      this.availableWorkflowPlugins = {};
      workflowPlugins.forEach(plugin => this.availableWorkflowPlugins[plugin.name] = plugin);
    }
  }

  newPluginRequested(event: CustomEvent) {
    const pluginName = event.detail.value.name;
    const pluginConfiguration = new WorkflowPlacePluginConfiguration();
    pluginConfiguration.name = pluginName;
    this.pluginsConfig.push(pluginConfiguration);
  }

  removePluginConfig(config: WorkflowPlacePluginConfiguration) {
    this.pluginsConfig.splice(this.pluginsConfig.indexOf(config), 1);
  }
}
