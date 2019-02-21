import {autoinject} from "aurelia-dependency-injection";
import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {ValidationController} from "aurelia-validation";
import {WorkflowPlacePluginConfiguration} from "workflows/workflow";
import {WorkflowPlugin} from "./workflow-plugin";

@autoinject
export class WorkflowPluginConfigurationDialog implements DialogComponentActivate<WorkflowPluginConfigurationDialogModel> {
    workflowPlacePluginConfiguration: WorkflowPlacePluginConfiguration;
    workflowPlugin: WorkflowPlugin;

    constructor(public dialogController: DialogController, public validationController: ValidationController) {
    }

    activate(model: WorkflowPluginConfigurationDialogModel) {
        this.workflowPlacePluginConfiguration = model.workflowPlacePluginConfiguration;
        this.workflowPlugin = model.workflowPlugin;
    }
}

export interface WorkflowPluginConfigurationDialogModel {
    workflowPlacePluginConfiguration: WorkflowPlacePluginConfiguration;
    workflowPlugin: WorkflowPlugin;
}
