import {autoinject} from "aurelia-dependency-injection";
import {bindable} from "aurelia-templating";
import {Modal} from "common/dialog/modal";
import {Workflow, WorkflowPlacePluginConfiguration} from "../../workflow";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowPlugin} from "./workflow-plugin";
import {WorkflowPluginConfigurationDialog, WorkflowPluginConfigurationDialogModel} from "./workflow-plugin-configuration-dialog";

@autoinject
export class WorkflowPluginsConfiguration {
  @bindable workflow: Workflow;
  @bindable pluginsConfig: WorkflowPlacePluginConfiguration[];

  private availableWorkflowPlugins: StringMap<WorkflowPlugin>;

  constructor(private workflowRepository: WorkflowRepository, private modal: Modal) {
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

  openPluginConfigurationDialog(workflowPlacePluginConfiguration: WorkflowPlacePluginConfiguration, workflowPlugin: WorkflowPlugin) {
    this.modal.open(WorkflowPluginConfigurationDialog, {
      workflowPlacePluginConfiguration: workflowPlacePluginConfiguration,
      workflowPlugin: workflowPlugin
    } as WorkflowPluginConfigurationDialogModel);
  }
}
