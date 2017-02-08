import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";

// I REALLY wanted to publish it as an NPM package, but I can't. Modern JS...
// https://gist.github.com/fracz/1536a2db1a2eb10ae7b8e41692a0a3ed
export class PromiseButton {
  @bindable onClick: () => any;

  @bindable idleIcon = 'fa-save';
  @bindable waitingIcon = 'fa-spinner fa-spin';
  @bindable successIcon = 'fa-check';
  @bindable failureIcon = 'fa-times';

  @bindable waitingText;
  @bindable successText;
  @bindable failureText;

  @bindable idleClass = 'btn btn-default';
  @bindable waitingClass = 'btn btn-default';
  @bindable successClass = 'btn btn-success';
  @bindable failureClass = 'btn btn-danger';

  @bindable resetTime = 2000;

  private state = 'idle';

  onButtonClicked() {
    if (typeof this.onClick === 'function') {
      this.state = 'waiting';
      Promise.resolve(this.onClick())
        .then(() => this.state = 'success')
        .catch(() => this.state = 'failure')
        .finally(() => this.resetStateAfterTimeout());
    }
  }

  private resetStateAfterTimeout() {
    setTimeout(() => this.state = 'idle', this.resetTime);
  }

  @computedFrom('state')
  get stateBasedCaption() {
    if (this.state == 'waiting' && this.waitingText) {
      return this.waitingText;
    } else if (this.state == 'success' && this.successText) {
      return this.successText;
    } else if (this.state == 'failure' && this.failureText) {
      return this.failureText;
    }
  }

  @computedFrom('state')
  get stateBasedIcon() {
    switch (this.state) {
      case 'waiting':
        return this.waitingIcon;
      case 'success':
        return this.successIcon;
      case 'failure':
        return this.failureIcon;
      default:
        return this.idleIcon;
    }
  }

  @computedFrom('state')
  get stateBasedCssClass() {
    switch (this.state) {
      case 'waiting':
        return this.waitingClass;
      case 'success':
        return this.successClass;
      case 'failure':
        return this.failureClass;
      default:
        return this.idleClass;
    }
  }
}
