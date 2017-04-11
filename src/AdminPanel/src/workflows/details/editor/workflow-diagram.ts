import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {Workflow, WorkflowPlace} from "../../workflow";
import {WorkflowGraph} from "./workflow-graph";
import {inject, NewInstance} from "aurelia-dependency-injection";

@inject(NewInstance.of(WorkflowGraph))
export class WorkflowDiagram implements ComponentAttached, ComponentDetached {
  diagramContainer: HTMLElement;
  @bindable workflow: Workflow;
  @bindable current: Array<WorkflowPlace>;
  @bindable editable: boolean = false;
  @bindable onGraphBuilt: (value?: {graph: WorkflowGraph}) => any = () => undefined;

  constructor(private graph: WorkflowGraph) {
  }

  attached() {
    this.graph.render(this.workflow, this.diagramContainer);
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
