import {DialogComponentActivate, DialogController} from "aurelia-dialog";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class CloneResourceDialog implements DialogComponentActivate<CloneResourceModel> {
  resourceLabel: string;
  cloneTimes: number = 1;
  validationError: boolean = false;

  constructor(public dialogController: DialogController) {
  }

  activate(model: CloneResourceModel) {
    this.resourceLabel = model.label;
  }

  confirm(): void {
    if (1 <= this.cloneTimes && this.cloneTimes <= 50) {
      this.dialogController.ok(this.cloneTimes);
    } else {
      this.validationError = true;
    }
  }
}

export interface CloneResourceModel {
  label: string;
}
