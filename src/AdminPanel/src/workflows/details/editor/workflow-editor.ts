import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowGraph} from "./workflow-graph";

@autoinject
export class WorkflowEditor {
  @bindable workflow: Workflow;

  selectedElement: WorkflowPlace|WorkflowTransition;
  simulating = false;
  availableTransitions: Array<string> = [];
  private graph: WorkflowGraph;
  private currentSimulationPlaces: Array<string>;
  private fetchingTransitions = false;

  constructor(private workflowRepository: WorkflowRepository) {
  }

  onGraphBuilt(graph: WorkflowGraph) {
    this.graph = graph;
    this.graph.onPlaceSelect(place => {
      this.workflow.places = this.graph.getPlaces();
      this.selectedElement = place;
    });
    this.graph.onTransitionSelect(transition => this.selectedElement = transition);
    this.graph.onDeselect(() => this.selectedElement = undefined);
  }

  saveSelectedElementChanges() {
    this.graph.updateElement(this.selectedElement);
  }

  save() {
    let workflow = this.graph.toWorkflowWithLayout();
    workflow.id = this.workflow.id;
    return this.workflowRepository.put(workflow).then(workflow => this.workflow = workflow);
  }

  private updateWorkflowBasedOnGraph() {
    this.workflow = $.extend(this.graph.toWorkflow(), {id: this.workflow.id});
  }

  toggleSimulation() {
    if (this.simulating) {
      this.currentSimulationPlaces = [];
      this.availableTransitions = [];
      this.simulating = false;
    } else {
      this.simulating = true;
      this.updateWorkflowBasedOnGraph();
      this.advanceSimulation();
    }
  }

  advanceSimulation(transitionId?: string) {
    this.fetchingTransitions = true;
    this.workflowRepository.simulate(this.workflow, this.currentSimulationPlaces, transitionId)
      .then(response => {
        this.availableTransitions = response.transitions;
        this.currentSimulationPlaces = response.places;
      })
      .finally(() => this.fetchingTransitions = false);
  }
}
