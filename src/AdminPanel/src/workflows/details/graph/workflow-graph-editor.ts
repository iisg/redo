import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {bindable, ComponentAttached, ComponentUnbind} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {WorkflowRepository} from "../../workflow-repository";
import {WorkflowGraph} from "./workflow-graph";
import {BindingSignaler} from "aurelia-templating-resources";
import {twoWay} from "common/components/binding-mode";
import {WorkflowGraphEditorReady, WorkflowGraphReady} from "./workflow-graph-events";
import {BindingEngine, Disposable} from "aurelia-binding";
import {WorkflowGraphManager} from "./workflow-graph-manager";
import {ChangeEvent} from "../../../common/events/change-event";
import {debounce} from "lodash";

@autoinject
export class WorkflowGraphEditor implements ComponentAttached, ComponentUnbind {
  @bindable(twoWay) workflow: Workflow;

  selectedElement: WorkflowPlace | WorkflowTransition;
  simulating = false;
  availableTransitions: Array<string> = [];
  graph: WorkflowGraph;
  currentSimulationPlaces: Array<string>;
  fetchingTransitions = false;
  simulationAllowed = false;

  private placesSubscription: Disposable;
  private transitionsSubscription: Disposable;

  constructor(private workflowRepository: WorkflowRepository,
              private signaler: BindingSignaler,
              private element: Element,
              private bindingEngine: BindingEngine,
              private graphManager: WorkflowGraphManager) {
  }

  attached(): void {
    this.workflowElemChanged();
  }

  private observeWorkflowPlaces(): void {
    this.disposeWorkflowPlacesSubscription();
    this.placesSubscription = this.bindingEngine
      .propertyObserver(this.workflow, 'places')
      .subscribe(() => this.workflowPlacesChanged());
    this.transitionsSubscription = this.bindingEngine
      .propertyObserver(this.workflow, 'transitions')
      .subscribe(() => this.workflowElemChanged());
  }

  private disposeWorkflowPlacesSubscription(): void {
    if (this.placesSubscription !== undefined) {
      this.placesSubscription.dispose();
      this.placesSubscription = undefined;
    }
    if (this.transitionsSubscription !== undefined) {
      this.transitionsSubscription.dispose();
      this.transitionsSubscription = undefined;
    }
  }

  workflowChanged(): void {
    this.observeWorkflowPlaces();
  }

  workflowPlacesChanged(): void {
    this.workflowElemChanged();
    this.graphManager.forEach(graph => graph.recalculateGraphPosition());
    this.dispatchChangedEvent(this.workflow);
  }

  workflowElemChanged(): void {
    this.simulationAllowed = !!this.workflow.places.length;
  }

  dispatchChangedEvent = debounce((value) => this.element.dispatchEvent(ChangeEvent.newInstance(value)), 10);

  unbind(): void {
    this.disposeWorkflowPlacesSubscription();
  }

  onGraphBuilt(event: WorkflowGraphReady) {
    this.graph = event.detail.graph;
    this.graph.onPlaceSelect(place => {
      this.updateWorkflowPlacesBasedOnGraph();
      this.selectedElement = this.findMatchingPlace(place);
    });
    this.graph.onTransitionSelect(transition => {
      this.selectedElement = transition;
    });
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
    if (withLayout) {
      this.workflow.diagram = workflow.diagram;
      this.workflow.thumbnail = workflow.thumbnail;
    }
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
