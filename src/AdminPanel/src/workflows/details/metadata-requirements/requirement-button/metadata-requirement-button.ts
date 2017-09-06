import {bindable, ComponentAttached} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {observable} from "aurelia-binding";
import {booleanAttribute} from "common/components/boolean-attribute";
import {noop} from "common/utils/function-utils";

export class MetadataRequirementButton implements ComponentAttached {
  @bindable({changeHandler: 'readState'}) model: number;
  @bindable(twoWay) requiredArray: number[] = [];
  @bindable(twoWay.and({changeHandler: 'readState'})) lockedArray: number[] = [];
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable onChange: () => void = noop;

  @observable state: RequirementState; // TODO turn into getter+setter when https://github.com/aurelia/templating/issues/561 is resolved

  container: Element;

  readState(): void {
    if (this.model == undefined) {
      return;
    }
    const isRequired = (this.requiredArray.indexOf(this.model) != -1);
    const isLocked = (this.lockedArray.indexOf(this.model) != -1);
    this.state = isLocked ? RequirementState.LOCKED
      : isRequired ? RequirementState.REQUIRED
      : RequirementState.OPTIONAL;
    if (isRequired && isLocked) {
      this.removeFromRequiredArray();
    }
  }

  requiredArrayChanged(): void {
    const isRequired = (this.requiredArray.indexOf(this.model) != -1);
    const isLocked = (this.lockedArray.indexOf(this.model) != -1);
    if (!isRequired || !isLocked) {
      this.readState();
      return;
    }
    this.state = RequirementState.REQUIRED;
    this.removeFromLockedArray();
  }

  click(): void {
    if (!this.disabled) {
      this.nextState();
    }
  }

  private nextState(): void {
    switch (this.state) {
      case RequirementState.OPTIONAL:
        this.state = RequirementState.REQUIRED;
        break;
      case RequirementState.REQUIRED:
        this.state = RequirementState.LOCKED;
        break;
      case RequirementState.LOCKED:
        this.state = RequirementState.OPTIONAL;
        break;
      default:
        throw new Error('Unknown state');
    }
  }

  get isRequired(): boolean {
    return this.state == RequirementState.REQUIRED;
  }

  get isLocked(): boolean {
    return this.state == RequirementState.LOCKED;
  }

  stateChanged(): void {
    this.removeFromArrays();
    if (this.state == RequirementState.REQUIRED) {
      this.requiredArray.push(this.model);
    } else if (this.state == RequirementState.LOCKED) {
      this.lockedArray.push(this.model);
    }
    this.onChange();
  }

  disabledChanged() {
    $(this.container).toggleClass('disabled', this.disabled);  // fails gently when this.element is undefined
  }

  attached(): void {
    this.disabledChanged();  // depends on this.element which is not available in binding stage, call it again
  }

  private removeFromArrays(): void {
    this.removeFromRequiredArray();
    this.removeFromLockedArray();
  }

  private removeFromRequiredArray(): void {
    const requiredIndex = this.requiredArray.indexOf(this.model);
    if (requiredIndex != -1) {
      this.requiredArray.splice(requiredIndex, 1);
    }
  }

  private removeFromLockedArray(): void {
    const lockedIndex = this.lockedArray.indexOf(this.model);
    if (lockedIndex != -1) {
      this.lockedArray.splice(lockedIndex, 1);
    }
  }
}

enum RequirementState {
  OPTIONAL,
  REQUIRED,
  LOCKED,
}
