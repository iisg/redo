import {WorkflowGraphDisplay} from "./workflow-graph-display";
import {removeValue} from "common/utils/array-utils";

export class WorkflowGraphManager {
  private graphs: WorkflowGraphDisplay[] = [];

  register(graph: WorkflowGraphDisplay): void {
    this.graphs.push(graph);
  }

  unregister(graph: WorkflowGraphDisplay): void {
    removeValue(this.graphs, graph);
  }

  forEach(callback: (graph: WorkflowGraphDisplay) => void) {
    this.graphs.forEach(graph => callback(graph));
  }
}
