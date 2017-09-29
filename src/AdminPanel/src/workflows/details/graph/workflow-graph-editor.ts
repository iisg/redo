import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowGraph} from "./workflow-graph";
import {BindingSignaler} from "aurelia-templating-resources";
import {twoWay} from "common/components/binding-mode";
import {WorkflowGraphEditorReady, WorkflowGraphReady} from "./workflow-graph-events";

@autoinject
export class WorkflowGraphEditor {
  @bindable(twoWay) workflow: Workflow;

  selectedElement: WorkflowPlace|WorkflowTransition;
  simulating = false;
  availableTransitions: Array<string> = [];
  graph: WorkflowGraph;
  currentSimulationPlaces: Array<string>;
  fetchingTransitions = false;

  constructor(private workflowRepository: WorkflowRepository, private signaler: BindingSignaler, private element: Element) {
  }

  onGraphBuilt(event: WorkflowGraphReady) {
    this.graph = event.detail.graph;
    this.graph.onPlaceSelect(place => {
      this.updateWorkflowPlacesBasedOnGraph();
      this.selectedElement = this.findMatchingPlace(place);
    });
    this.graph.onTransitionSelect(transition => this.selectedElement = transition);
    this.graph.onDeselect(() => this.selectedElement = undefined);
    this.graph.onPlacesChangedByUser = () => this.updateWorkflowBasedOnGraph();
    this.element.dispatchEvent(WorkflowGraphEditorReady.newInstance(this));
  }

  saveSelectedElementChanges() {
    this.graph.updateElement(this.selectedElement);
  }

  public updateWorkflowBasedOnGraph(withLayout: boolean = false): void {
    const workflow = withLayout ? this.graph.toWorkflowWithLayout() : this.graph.toWorkflow();
    this.copyPlaceRequirementArrays(this.workflow.places, workflow.places);
    this.workflow.places = workflow.places;
    this.workflow.transitions = workflow.transitions;
    this.workflow.diagram = workflow.diagram;
    this.workflow.thumbnail = workflow.thumbnail;
  }

  private updateWorkflowPlacesBasedOnGraph() {
    const places: WorkflowPlace[] = this.graph.getPlaces();
    this.copyPlaceRequirementArrays(this.workflow.places, places);
    this.workflow.places = places;
  }

  private copyPlaceRequirementArrays(sources: WorkflowPlace[], targets: WorkflowPlace[]): void {
    const sourceMap = this.getPlacesLookupMap(sources);
    for (const target of targets) {
      const source = sourceMap[target.id];
      target.restrictingMetadataIds = source ? source.restrictingMetadataIds : {};
    }
  }

  private findMatchingPlace(similarPlace: WorkflowPlace): WorkflowPlace {
    for (const place of this.workflow.places) {
      if (place.id == similarPlace.id) {
        return place;
      }
    }
    return undefined;
  }

  private getPlacesLookupMap(places: WorkflowPlace[]): NumberMap<WorkflowPlace> {
    const result = {};
    for (const place of places) {
      result[place.id] = place;
    }
    return result;
  }

  signalWorkflowPlacesUpdated() {
    this.signaler.signal('workflow-places-updated');
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
