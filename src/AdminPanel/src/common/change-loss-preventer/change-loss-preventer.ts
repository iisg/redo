import {Alert, AlertOptions} from "../dialog/alert";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {ChangeLossPreventerForm} from "../form/change-loss-preventer-form";
import {NavigationInstruction, Next, PipelineStep} from "aurelia-router";

@autoinject
export class ChangeLossPreventer implements PipelineStep {
  private guardedForm: ChangeLossPreventerForm;

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    return this.canLeaveView().then(canDeactivate => canDeactivate ? next() : next.cancel());
  }

  private readonly titleLabel = 'Your changes in form will be lost';

  constructor(private i18n: I18N, private alert: Alert) {
  }

  enable(entityForm: ChangeLossPreventerForm) {
    this.guardedForm = entityForm;
  }

  disable() {
    this.guardedForm = undefined;
  }

  public canLeaveView(): Promise<boolean> {
    return Promise.resolve(!this.guardedForm || !this.guardedForm.isDirty() || this.askIfCanLeave());
  }

  private askIfCanLeave(): Promise<boolean> {
    const title = this.i18n.tr(this.titleLabel);
    const alertOptions: AlertOptions = {
      type: 'question',
      cancelButtonText: this.i18n.tr('Stay'),
      confirmButtonClass: 'danger'
    };
    return this.alert.show(alertOptions, title).then(decision => {
      if (decision) {
        this.disable();
      }
      return !!decision;
    });
  }
}
