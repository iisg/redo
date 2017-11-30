import {bindable} from "aurelia-templating";
import {WorkflowTransition, WorkflowPlace} from "workflows/workflow";
import {autoinject} from "aurelia-dependency-injection";
import {VoidFunction} from "common/utils/function-utils";
import {computedFrom} from "aurelia-binding";
import {inArray} from "../../common/utils/array-utils";

@autoinject
export class TransitionButton {
  @bindable submitting: boolean;
  @bindable transition: WorkflowTransition;
  @bindable places: WorkflowPlace[] = [];
  @bindable canApplyTransition: boolean;
  @bindable applyTransition: VoidFunction;

  @computedFrom('transition', 'places')
  get tos(): WorkflowPlace[] {
    if (this.transition) {
      let tos = this.transition.tos;
      return this.places.filter(place => inArray(place.id, tos));
    } else {
      return [];
    }
  }
}
