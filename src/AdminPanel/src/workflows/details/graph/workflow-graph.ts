import * as cytoscape from "cytoscape";
import * as contextMenus from "cytoscape-context-menus";
import * as edgeHandles from "cytoscape-edgehandles";
import * as autopanOnDrag from "cytoscape-autopan-on-drag";
import {MultilingualText} from 'resources-config/metadata/metadata';
import {workflowGraphDefaultStylesheet} from "./workflow-graph-stylesheet";
import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {generateId} from "common/utils/string-utils";
import {I18N} from "aurelia-i18n";
import {autoinject} from "aurelia-dependency-injection";
import {InCurrentLanguageValueConverter} from "resources-config/multilingual-field/in-current-language";
import {deepCopy, mapToArray, values} from "common/utils/object-utils";
import {noop, VoidFunction} from "common/utils/function-utils";
import {ResourceRepository} from "../../../resources/resource-repository";
import {Resource} from "../../../resources/resource";
import {WorkflowPlaceDeletionAlert} from "./workflow-place-deletion-alert";
import {PageResult} from "../../../resources/page-result";

@autoinject
export class WorkflowGraph {
  // mandatory for unit testing; unit tests do not see the imports above; have no idea why
  public static CYTOSCAPE_FACTORY: Cytoscape.Static;
  isProcessingWorkflowPlaceRemoval: boolean = false;

  private cytoscape: Cytoscape.Instance;

  private readyPromiseResolve;
  public readonly ready = new Promise((resolve) => this.readyPromiseResolve = resolve);

  public onPlacesChangedByUser: VoidFunction = noop;

  constructor(private i18n: I18N,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private resourceRepository: ResourceRepository,
              private workflowPlaceDeletionAlert: WorkflowPlaceDeletionAlert) {
  }

  render(workflow: Workflow, container?: HTMLElement, editable: boolean = false) {
    this.cytoscape = WorkflowGraph.CYTOSCAPE_FACTORY({
      container: container,
      layout: {
        name: 'concentric'
      },
      style: workflowGraphDefaultStylesheet,
      maxZoom: 4,
      minZoom: .2,
      autoungrabify: !editable,
      autounselectify: !editable,
    });
    this.cytoscape.autopanOnDrag();
    this.cytoscape.on('tapend', () => this.fit());
    this.drawWorkflow(workflow);
    if (editable) {
      this.edgeHandles();
      this.contextMenu();
    }
    this.readyPromiseResolve();
    this.recalculatePosition();
  }

  recalculatePosition(): void {
    setTimeout(() => this.cytoscape.resize(), 500); // fix misclicks due to slide-in animation; timeout was chosen by experiment
  }

  private drawWorkflow(workflow: Workflow) {
    workflow.places.forEach(place => this.addPlace(place));
    workflow.transitions.forEach(transition => this.addTransition(transition));
    try {
      let positions = JSON.parse(workflow.diagram);
      this.cytoscape.nodes().forEach(node => node.position(positions[node.id()]));
    } catch (error) {
      // Saved diagram is invalid. Somebody messed up the data? Ignore it and leave the default layout.
    }
    this.fit();
  }

  private edgeHandles() {
    this.cytoscape.edgehandles({
      preview: false,
      cxt: false,
      handleSize: 10,
      handleColor: '#f0ff31',
      handleOutlineColor: '#000000',
      handleOutlineWidth: 1,
      toggleOffOnLeave: true,
      complete: (sourceNode, targetNodes, addedEntities) => {
        let edge = addedEntities[0];
        edge.data('label', {});
        this.deselectAll();
        edge.select();
      },
    });
  }

  private contextMenu() {
    this.cytoscape.contextMenus({
      menuItems: [
        {
          title: this.i18n.tr('Mark as initial'),
          selector: 'node[^initial]',
          onClickFunction: ({cyTarget: node}) => {
            this.markAsInitial(this.nodeToPlace(node));
          },
        },
        {
          title: this.i18n.tr('Remove'),
          selector: 'node[^initial], edge',
          onClickFunction: ({cyTarget: element}) => {
            this.isProcessingWorkflowPlaceRemoval = true;
            this.getResourcesInWorkflowPlace(element).then(
              resources => {
                this.isProcessingWorkflowPlaceRemoval = false;
                if (resources.total === 0) {
                  this.cytoscape.remove(element);
                }
                else {
                  const resourceCount = resources.total;
                  this.workflowPlaceDeletionAlert
                    .showWorkflowPlaceDeletionAlert(resourceCount, resources.slice(0, Math.min(3, resourceCount)));
                }
                this.fit();
                this.onPlacesChangedByUser();
              }
            );
          },
        },
        {
          title: this.i18n.tr('New place'),
          coreAsWell: true,
          onClickFunction: ({cyRenderedPosition: position}) => {
            let newPlace = this.addPlace({
              id: generateId(''),
              label: {},
              restrictingMetadataIds: {},
              pluginsConfig: {},
            });
            let newNode = this.$(newPlace);
            newNode.renderedPosition(position);
            this.deselectAll();
            newNode.select();
            this.fit();
            this.onPlacesChangedByUser();
          }
        }
      ]
    });

  }

  private async getResourcesInWorkflowPlace(element): Promise<PageResult<Resource>> {
    let workflowPlace: WorkflowPlace = this.nodeToPlace(element);
    return await this.resourceRepository.getListQuery()
      .filterByWorkflowPlacesIds([workflowPlace.id])
      .setResultsPerPage(1)
      .get();
  }

  public addPlace(place: WorkflowPlace): WorkflowPlace {
    this.cytoscape.add([{group: "nodes", data: {id: place.id, label: place.label}}]);
    if (this.cytoscape.nodes().length == 1) {
      this.markAsInitial(place);
    }
    this.updateElement(place);
    return place;
  }

  public addTransition(transition: WorkflowTransition) {
    for (let to of transition.tos) {
      for (let from of transition.froms) {
        let edgeId = transition.id;
        if (this.cytoscape.$('#' + edgeId).length > 0) {
          edgeId = generateId('');
        }
        this.cytoscape.add([{
          group: "edges",
          data: {id: edgeId, label: deepCopy(transition.label), source: from, target: to}
        }]);
        this.updateElement(this.nodeToPlace(this.cytoscape.$('#' + edgeId)));
      }
    }
  }

  private markAsInitial(place: WorkflowPlace) {
    let currentInitial = this.cytoscape.$('.initial');
    if (currentInitial.length) {
      currentInitial.removeClass('initial');
      currentInitial.data().initial = undefined;
    }
    this.$(place).addClass('initial');
    this.$(place).data('initial', true);
  }

  private fit() {
    this.cytoscape.animate({fit: {padding: 20}, easing: 'ease'});
  }

  public onPlaceSelect(callback: (place: WorkflowPlace) => any) {
    this.cytoscape.on('select', 'node', ({cyTarget: node}) => callback(this.nodeToPlace(node)));
  }

  public onTransitionSelect(callback: (transition: WorkflowTransition) => any) {
    this.cytoscape.on('select', 'edge', ({cyTarget: edge}) => callback(this.edgeToTransition(edge)));
  }

  public onDeselect(callback: VoidFunction) {
    this.cytoscape.on('unselect', callback);
  }

  public highlightCurrent(places?: Array<WorkflowPlace>) {
    this.cytoscape.$(".current").removeClass('current');
    if (places) {
      for (let place of places) {
        this.$(place).addClass('current');
      }
    }
  }

  private getCurrentPlaces(): Array<WorkflowPlace> {
    return this.cytoscape.$(".current").map(this.nodeToPlace);
  }

  private $(element: WorkflowPlace | WorkflowTransition): any {
    return this.cytoscape.$(`#${element.id}`);
  }

  public toWorkflow(): Workflow {
    let workflow = new Workflow();
    workflow.places = this.getPlaces();
    workflow.transitions = this.getTransitions();
    return workflow;
  }

  public toWorkflowWithLayout() {
    let workflow = this.toWorkflow();
    let positions = {};
    this.cytoscape.nodes().forEach(node => positions[node.id()] = node.position());
    workflow.diagram = JSON.stringify(positions);
    workflow.thumbnail = this.toPng();
    return workflow;
  }

  public getPlaces(): Array<WorkflowPlace> {
    let places: Array<WorkflowPlace> = [];
    for (let node of this.cytoscape.nodes()) {
      let place = this.nodeToPlace(node);
      if (node.data('initial')) {
        places.unshift(place);
      } else {
        places.push(place);
      }
    }
    return places;
  }

  private nodeToPlace(node): WorkflowPlace {
    return new WorkflowPlace(node.id(), node.data('label'), {}, node.data('pluginsConfig'));
  }

  private mergeTransitionsByLabel(transitions: WorkflowTransition[], mergeBy: 'froms' | 'tos'): WorkflowTransition[] {
    const deduplicatedEdges: { [s: string]: WorkflowTransition } = {};
    const opposite = mergeBy == 'froms' ? 'tos' : 'froms';
    for (let transition of transitions) {
      let edgeVisualId = transition[mergeBy][0] + '_' + this.getLabelId(transition.label);
      if (deduplicatedEdges[edgeVisualId]) {
        deduplicatedEdges[edgeVisualId][opposite].push(transition[opposite][0]);
      } else {
        deduplicatedEdges[edgeVisualId] = transition;
      }
    }
    return values(deduplicatedEdges);
  }

  private getLabelId(label: MultilingualText): string {
    return mapToArray(label, (language, label) => language + '-' + label).join('_');
  }

  public getTransitions(): Array<WorkflowTransition> {
    let allTransitions: WorkflowTransition[] = this.cytoscape.edges().map(this.edgeToTransition);
    allTransitions = this.mergeTransitionsByLabel(allTransitions, 'froms');
    return this.mergeTransitionsByLabel(allTransitions, 'tos');
  }

  private edgeToTransition(edge): WorkflowTransition {
    return $.extend(new WorkflowTransition(), {
      id: edge.id(),
      label: edge.data('label'),
      froms: [edge.source().id()],
      tos: [edge.target().id()]
    });
  }

  private deselectAll() {
    this.cytoscape.elements().unselect();
  }

  public updateElement(element: WorkflowPlace | WorkflowTransition) {
    let graphElement = this.$(element);
    graphElement.data('label', element.label);
    let labelToDisplay = this.inCurrentLanguage.toView(graphElement.data('label'));
    if (element['pluginsConfig']) {
      graphElement.data('pluginsConfig', element['pluginsConfig']);
    }
    graphElement.data('labelToDisplay', labelToDisplay);
  }

  private toPng() {
    let currentPlaces = this.getCurrentPlaces();
    this.highlightCurrent();
    this.deselectAll();
    let png = this.cytoscape.png({maxHeight: 100, full: true});
    this.highlightCurrent(currentPlaces);
    return png;
  }

  public destroy(): void {
    this.cytoscape.contextMenus('get').destroy();
    this.cytoscape.destroy();
  }
}

if (typeof cytoscape == 'function') {
  WorkflowGraph.CYTOSCAPE_FACTORY = cytoscape;
  contextMenus(cytoscape, $);
  edgeHandles(cytoscape);
  autopanOnDrag(cytoscape);
}
