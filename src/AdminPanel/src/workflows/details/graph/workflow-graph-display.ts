import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {Workflow, WorkflowPlace} from "../../workflow";
import {WorkflowGraph} from "./workflow-graph";
import {inject, NewInstance} from "aurelia-dependency-injection";
import {noop} from "common/utils/function-utils";
import {booleanAttribute} from "common/components/boolean-attribute";

@inject(NewInstance.of(WorkflowGraph))
export class WorkflowGraphDisplay implements ComponentAttached, ComponentDetached {
  diagramContainer: HTMLElement;
  @bindable workflow: Workflow;
  @bindable current: WorkflowPlace[];
  @bindable @booleanAttribute editable: boolean = false;
  @bindable onGraphBuilt: (value?: {graph: WorkflowGraph}) => void = noop;

  constructor(private graph: WorkflowGraph) {
  }

  attached() {
    this.graph.render(this.workflow, this.diagramContainer, this.editable);
    this.currentChanged();
    this.onGraphBuilt({graph: this.graph});
  }

  detached(): void {
    this.graph.destroy();
  }

  currentChanged() {
    this.graph.ready.then(() => this.graph.highlightCurrent(this.current));
  }
}
