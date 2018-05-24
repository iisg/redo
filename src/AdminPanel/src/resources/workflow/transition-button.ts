import {bindable} from "aurelia-templating";
import {WorkflowPlace, WorkflowTransition} from "workflows/workflow";
import {VoidFunction} from "common/utils/function-utils";
import {computedFrom} from "aurelia-binding";
import {inArray} from "../../common/utils/array-utils";

export class TransitionButton {
  @bindable submitting: boolean;
  @bindable transition: WorkflowTransition;
  @bindable places: WorkflowPlace[] = [];
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
