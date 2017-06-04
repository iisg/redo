import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowGraph} from "./workflow-graph";
import {BindingSignaler} from "aurelia-templating-resources";
import {bindingMode} from "aurelia-binding";

@autoinject
export class WorkflowEditor {
  @bindable({defaultBindingMode: bindingMode.twoWay}) workflow: Workflow;

  selectedElement: WorkflowPlace|WorkflowTransition;
  simulating = false;
  availableTransitions: Array<string> = [];
  private graph: WorkflowGraph;
  private currentSimulationPlaces: Array<string>;
  private fetchingTransitions = false;

  constructor(private workflowRepository: WorkflowRepository, private signaler: BindingSignaler) {
  }

  onGraphBuilt(graph: WorkflowGraph) {
    this.graph = graph;
    this.graph.onPlaceSelect(place => {
      this.updateWorkflowPlacesBasedOnGraph();
      this.selectedElement = this.findMatchingPlace(place);
    });
    this.graph.onTransitionSelect(transition => this.selectedElement = transition);
    this.graph.onDeselect(() => this.selectedElement = undefined);
    this.graph.onPlacesChangedByUser = () => this.updateWorkflowBasedOnGraph();
  }

  saveSelectedElementChanges() {
    this.graph.updateElement(this.selectedElement);
  }

  save() {
    this.updateWorkflowBasedOnGraph(true);
    return this.workflowRepository.put(this.workflow).then(workflow => this.workflow = workflow);
  }

  private updateWorkflowBasedOnGraph(withLayout: boolean = false): void {
    const workflow = withLayout ? this.graph.toWorkflowWithLayout() : this.graph.toWorkflow();
    this.copyRequiredMetadataIds(this.workflow.places, workflow.places);
    this.workflow.places = workflow.places;
    this.workflow.transitions = workflow.transitions;
    this.workflow.diagram = workflow.diagram;
    this.workflow.thumbnail = workflow.thumbnail;
  }

  private updateWorkflowPlacesBasedOnGraph() {
    const places: WorkflowPlace[] = this.graph.getPlaces();
    this.copyRequiredMetadataIds(this.workflow.places, places);
    this.workflow.places = places;
  }

  private copyRequiredMetadataIds(sources: WorkflowPlace[], targets: WorkflowPlace[]): void {
    const sourceMap = this.getPlacesLookupMap(sources);
    for (const target of targets) {
      const source = sourceMap[target.id];
      target.requiredMetadataIds = source ? source.requiredMetadataIds : [];
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
