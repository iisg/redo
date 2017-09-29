import {DOM} from "aurelia-framework";
import {WorkflowGraph} from "./workflow-graph";
import {WorkflowGraphEditor} from "./workflow-graph-editor";

export class WorkflowGraphReady {
  bubbles = true;
  detail: { graph: WorkflowGraph };

  private constructor(graph: WorkflowGraph) {
    this.detail = {graph};
  }

  static newInstance(graph: WorkflowGraph): Event {
    return DOM.createCustomEvent('workflow-graph-ready', new WorkflowGraphReady(graph));
  }
}

export class WorkflowGraphEditorReady {
  bubbles = true;
  detail: { editor: WorkflowGraphEditor };

  private constructor(editor: WorkflowGraphEditor) {
    this.detail = {editor};
  }

  static newInstance(editor: WorkflowGraphEditor): Event {
    return DOM.createCustomEvent('workflow-graph-editor-ready', new WorkflowGraphEditorReady(editor));
  }
}
