import {bindable} from "aurelia-templating";
import {WorkflowTransition, WorkflowPlace} from "../../workflow";
import {computedFrom} from "aurelia-binding";
import {inArray} from "../../../common/utils/array-utils";

export class WorkflowFromsTosDisplay {
  @bindable transition: WorkflowTransition;
  @bindable places: WorkflowPlace[] = [];

  @computedFrom('places', 'transition')
  get froms(): WorkflowPlace[] {
    if (this.transition) {
      let froms = this.transition.froms;
      return this.places.filter(place => inArray(place.id, froms));
    } else {
      return [];
    }
  }

  @computedFrom('places', 'transition')
  get tos(): WorkflowPlace[] {
    if (this.transition) {
      let tos = this.transition.tos;
      return this.places.filter(place => inArray(place.id, tos));
    } else {
      return [];
    }
  }
}