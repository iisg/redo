import {Workflow, WorkflowPlace} from "workflows/workflow";
import {unique} from "common/utils/array-utils";

export class WorkflowPlaceSorter {
  getOrderedPlaces(workflow: Workflow) {
    if (workflow.places.length == 0) {
      return [];
    }
    const nodeMap = this.getGraphNodeMap(workflow);
    const orderedNodes: GraphNode[] = this.iterativeDfs(nodeMap[workflow.places[0].id], nodeMap);
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

  private iterativeDfs(node: GraphNode, allNodes: StringMap<GraphNode>): GraphNode[] {
    let sortedNodes: GraphNode[] = [];
    let nodeStack: GraphNode[] = [node];
    while (nodeStack.length != 0) {
      let currentNode = nodeStack.pop();
      if (sortedNodes.indexOf(currentNode) == -1) {
        let successorNodes = currentNode.possibleTransitions.map(id => allNodes[id]);
        sortedNodes.push(currentNode);
        nodeStack.push.apply(nodeStack, successorNodes);
      }
    }
    return sortedNodes;
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
