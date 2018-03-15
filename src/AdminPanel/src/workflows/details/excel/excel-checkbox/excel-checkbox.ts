import {bindable} from "aurelia-templating";
import {twoWay} from "common/components/binding-mode";
import {observable, computedFrom} from "aurelia-binding";
import {booleanAttribute} from "common/components/boolean-attribute";
import {noop} from "common/utils/function-utils";
import {successor} from "common/utils/enum-utils";
import {RestrictingMetadataIdMap, RequirementState} from "workflows/workflow";

export class ExcelCheckbox {
  @bindable({changeHandler: 'updateInternalState'}) model: number;
  @bindable @booleanAttribute assigneeAllowed: boolean = false;
  @bindable(twoWay.and({changeHandler: 'updateInternalState'})) states: RestrictingMetadataIdMap = {};
  @bindable @booleanAttribute disabled: boolean = false;
  @bindable onChange: () => void = noop;

  container: Element;
  @observable state: RequirementState = RequirementState.OPTIONAL;

  updateInternalState() {
    if (!(this.model in this.states) && this.model !== undefined) {
      this.states[this.model] = RequirementState.OPTIONAL;
    }
    this.state = this.states[this.model];
  }

  nextState(): void {
    let next: RequirementState = successor(this.state, RequirementState);
    if (next == RequirementState.ASSIGNEE && !this.assigneeAllowed) {
      next = successor(next, RequirementState);
      next = successor(next, RequirementState);
    }
    this.setState(next);
    this.updateInternalState();
    this.onChange();
  }

  private setState(state: RequirementState) {
    this.states[this.model] = state;
  }

  @computedFrom('state')
  get isOptional(): boolean {
    return !this.isRequired && !this.isLocked && !this.isAssignee && !this.isAutoAssign;
  }

  @computedFrom('state')
  get isRequired(): boolean {
    return this.states[this.model] == RequirementState.REQUIRED;
  }

  @computedFrom('state')
  get isLocked(): boolean {
    return this.states[this.model] == RequirementState.LOCKED;
  }

  @computedFrom('state')
  get isAssignee(): boolean {
    return this.states[this.model] == RequirementState.ASSIGNEE;
  }

  @computedFrom('state')
  get isAutoAssign(): boolean {
    return this.states[this.model] == RequirementState.AUTOASSIGN;
  }
}
