import {WorkflowPlace} from "../../../../workflows/workflow";
import {twoWay} from "../../binding-mode";
import {bindable} from "aurelia-templating";

export class WorkflowPlaceChooser {
  @bindable workflowPlaces: WorkflowPlace[];
  @bindable(twoWay) value: WorkflowPlace[];
}
