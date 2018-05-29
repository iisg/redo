import {computedFrom} from "aurelia-binding";
import {bindable} from "aurelia-templating";
import {VoidFunction} from "common/utils/function-utils";
import {WorkflowPlace, WorkflowTransition} from "workflows/workflow";
import {inArray} from "../../common/utils/array-utils";

export class TransitionButton {
  @bindable transition: WorkflowTransition;
  @bindable places: WorkflowPlace[] = [];
  @bindable applyTransition: VoidFunction;
  @bindable submitting: boolean;

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
