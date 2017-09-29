import {bindable, ComponentAttached, ComponentDetached} from "aurelia-templating";
import {Workflow, WorkflowPlace} from "../../workflow";
import {WorkflowGraph} from "./workflow-graph";
import {inject, NewInstance} from "aurelia-dependency-injection";
import {booleanAttribute} from "common/components/boolean-attribute";
import {WorkflowGraphReady} from "./workflow-graph-events";

@inject(NewInstance.of(WorkflowGraph), Element)
export class WorkflowGraphDisplay implements ComponentAttached, ComponentDetached {
  diagramContainer: HTMLElement;
  @bindable workflow: Workflow;
  @bindable current: WorkflowPlace[];
  @bindable @booleanAttribute editable: boolean = false;

  constructor(private graph: WorkflowGraph, private element: Element) {
  }

  attached() {
    this.graph.render(this.workflow, this.diagramContainer, this.editable);
    this.currentChanged();
    this.element.dispatchEvent(WorkflowGraphReady.newInstance(this.graph));
  }

  detached(): void {
    this.graph.destroy();
  }

  currentChanged() {
    this.graph.ready.then(() => this.graph.highlightCurrent(this.current));
  }
}
