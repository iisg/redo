import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {NavigationInstruction, Next, PipelineStep} from "aurelia-router";
import {Alert, AlertOptions} from "../dialog/alert";
import {ChangeLossPreventerForm} from "../form/change-loss-preventer-form";

@autoinject
export class ChangeLossPreventer implements PipelineStep {
  private readonly titleLabel = 'Changes in the form will be lost';

  private guardedForm: ChangeLossPreventerForm;

  constructor(private i18n: I18N, private alert: Alert) {
    window.onbeforeunload = (e) => {
      if (this.guardedForm && this.guardedForm.isDirty()) {
        const dialogText = this.i18n.tr(this.titleLabel);
        e.returnValue = dialogText;
        return dialogText;
      }
    };
  }

  run(instruction: NavigationInstruction, next: Next): Promise<any> {
    return this.canLeaveView().then(canDeactivate => canDeactivate ? next() : next.cancel());
  }

  enable(entityForm: ChangeLossPreventerForm) {
    this.guardedForm = entityForm;
    this.guardedForm.dirty = false;
  }

  disable() {
    this.guardedForm = undefined;
  }

  canLeaveView(): Promise<boolean> {
    return Promise.resolve(!this.guardedForm || !this.guardedForm.isDirty() || this.askIfCanLeave());
  }

  private askIfCanLeave(): Promise<boolean> {
    const title = this.i18n.tr(this.titleLabel);
    const alertOptions: AlertOptions = {
      type: 'warning',
      confirmButtonText: this.i18n.tr('Discard changes'),
      confirmButtonClass: 'red',
      showCancelButton: true,
      cancelButtonText: this.i18n.tr('Stay')
    };
    return this.alert.show(alertOptions, title).then(() => {
      this.disable();
      return true;
    }).catch(() => {
      return false;
    });
  }
}
