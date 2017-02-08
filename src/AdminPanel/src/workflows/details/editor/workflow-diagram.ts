import {bindable} from "aurelia-templating";
import {Workflow, WorkflowPlace} from "../../workflow";
import {WorkflowGraph} from "./workflow-graph";
import {inject, NewInstance} from "aurelia-dependency-injection";

@inject(NewInstance.of(WorkflowGraph))
export class WorkflowDiagram {
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

  currentChanged() {
    if (this.graph) {
      this.graph.highlightCurrent(this.current);
    }
  }
}
