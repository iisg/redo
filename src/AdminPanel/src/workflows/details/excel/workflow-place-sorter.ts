import {Workflow, WorkflowPlace} from "workflows/workflow";
import {unique} from "common/utils/array-utils";

export class WorkflowPlaceSorter {
  getOrderedPlaces(workflow: Workflow) {
    if (workflow.places.length == 0) {
      return [];
    }
    const nodeMap = this.getGraphNodeMap(workflow);
    const orderedNodes: GraphNode[] = this.iterativeBfs(nodeMap[workflow.places[0].id], nodeMap);
    const orderedNodesWithLoose = this.addLooseNodes(orderedNodes, nodeMap);
    return orderedNodesWithLoose.map(node => node.place);
  }

  private getGraphNodeMap(workflow: Workflow): StringMap<GraphNode> {
    let nodeMap: StringMap<GraphNode> = {};
    for (const place of workflow.places) {
      nodeMap[place.id] = new GraphNode(place);
    }
    for (const transition of workflow.transitions) {
      for (const sourceId of transition.froms) {
        nodeMap[sourceId].addTransitions(transition.tos);
      }
    }
    return nodeMap;
  }

  private iterativeBfs(node: GraphNode, allNodes: StringMap<GraphNode>): GraphNode[] {
    let previousIteration = [];
    let currentIteration = [node];
    while (previousIteration.length < currentIteration.length) {
      previousIteration = currentIteration;
      currentIteration = this.addSuccessors(previousIteration, allNodes);
    }
    return currentIteration;
  }

  private addSuccessors(nodes: GraphNode[], allNodes: StringMap<GraphNode>): GraphNode[] {
    const successors: GraphNode[] = nodes
      .map(node => node.possibleTransitions)              // get all successor arrays
      .reduce((array1, array2) => array1.concat(array2))  // concat into one array
      .map(id => allNodes[id]);                           // get actual nodes for IDs
    return unique(nodes.concat(successors));
  }

  private addLooseNodes(nodes: GraphNode[], allNodesMap: StringMap<GraphNode>) {
    const allNodes = Object.keys(allNodesMap).map(id => allNodesMap[id]);
    return unique(nodes.concat(allNodes));
  }
}

class GraphNode {
  possibleTransitions: string[] = [];

  constructor(public place: WorkflowPlace) {
  }

  addTransitions(targetIds: string[]) {
    this.possibleTransitions = this.possibleTransitions.concat(targetIds);
  }
}
