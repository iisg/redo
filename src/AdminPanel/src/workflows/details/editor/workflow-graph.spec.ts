import {WorkflowGraph} from "./workflow-graph";
import {Workflow} from "../../workflow";
import {I18N} from "aurelia-i18n";
import {InCurrentLanguageValueConverter} from "../../../resources-config/multilingual-field/in-current-language";

describe(WorkflowGraph.name, () => {
  let cytoscape: Cytoscape.Instance;
  let i18n: I18N;
  let inCurrentLanguageValueConverter: InCurrentLanguageValueConverter;
  let graph: WorkflowGraph;

  beforeEach(() => {
    cytoscape = jasmine.createSpyObj('cytoscape', ['autopanOnDrag', 'on', 'animate', 'edgehandles', 'contextMenus', 'nodes', 'edges']);
    i18n = jasmine.createSpyObj('i18n', ['tr']);
    WorkflowGraph.CYTOSCAPE_FACTORY = () => cytoscape;
    inCurrentLanguageValueConverter = jasmine.createSpyObj('inCurrentLanguageValueConverter', ['toView']);
    inCurrentLanguageValueConverter.toView['and'].callFake(label => label);
    graph = new WorkflowGraph(i18n, inCurrentLanguageValueConverter);
    graph.render(new Workflow);
  });

  it("initializes the graph", () => {
    expect(cytoscape.autopanOnDrag).toHaveBeenCalled();
  });

  describe("getTransitions", () => {

    let edge = (label, from, to) => {
      return {
        id: () => label,
        data: () => label,
        source: () => {
          return {
            id: () => from,
          };
        },
        target: () => {
          return {
            id: () => to,
          };
        }
      };
    };

    it("gets the simple transitions", () => {
      cytoscape.edges['and'].returnValue([
        edge('A', 'A', 'B'),
        edge('B', 'B', 'C'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(2);
      expect(transitions[0].label).toEqual('A');
      expect(transitions[1].label).toEqual('B');
      expect(transitions[0].froms).toEqual(['A']);
      expect(transitions[0].tos).toEqual(['B']);
      expect(transitions[1].froms).toEqual(['B']);
      expect(transitions[1].tos).toEqual(['C']);
    });

    it("gets multiple outs from place", () => {
      cytoscape.edges['and'].returnValue([
        edge('A', 'A', 'B'),
        edge('B', 'A', 'C'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(2);
      expect(transitions[0].label).toEqual('A');
      expect(transitions[1].label).toEqual('B');
      expect(transitions[0].froms).toEqual(['A']);
      expect(transitions[0].tos).toEqual(['B']);
      expect(transitions[1].froms).toEqual(['A']);
      expect(transitions[1].tos).toEqual(['C']);
    });

    it("handles multiple targets", () => {
      cytoscape.edges['and'].returnValue([
        edge('A', 'A', 'B'),
        edge('A', 'A', 'C'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(1);
      expect(transitions[0].label).toEqual('A');
      expect(transitions[0].froms).toEqual(['A']);
      expect(transitions[0].tos).toEqual(['B', 'C']);
    });

    it("handles transitions with the same name but different source", () => {
      cytoscape.edges['and'].returnValue([
        edge('A', 'A', 'B'),
        edge('B', 'B', 'C'),
        edge('A', 'C', 'D'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(3);
    });

    it("handles multiple sources", () => {
      cytoscape.edges['and'].returnValue([
        edge('A', 'A', 'B'),
        edge('A', 'C', 'B'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(1);
      expect(transitions[0].label).toEqual('A');
      expect(transitions[0].froms).toEqual(['A', 'C']);
      expect(transitions[0].tos).toEqual(['B']);
    });

    // http://symfony-workflow-demo.herokuapp.com/article
    it("handles example journalist workflow", () => {
      cytoscape.edges['and'].returnValue([
        edge('request_review', 'draft', 'wait_for_journalist'),
        edge('request_review', 'draft', 'wait_for_spellchecker'),
        edge('journalist_approval', 'wait_for_journalist', 'approved_by_journalist'),
        edge('spellchecker_approval', 'wait_for_spellchecker', 'approved_by_spellchecker'),
        edge('publish', 'approved_by_journalist', 'published'),
        edge('publish', 'approved_by_spellchecker', 'published'),
      ]);
      let transitions = graph.getTransitions();
      expect(transitions.length).toBe(4);
      expect(transitions[0].label).toEqual('request_review');
      expect(transitions[0].froms).toEqual(['draft']);
      expect(transitions[0].tos).toEqual(['wait_for_journalist', 'wait_for_spellchecker']);
      expect(transitions[1].tos).toEqual(['approved_by_journalist']);
      expect(transitions[2].tos).toEqual(['approved_by_spellchecker']);
      expect(transitions[3].froms).toEqual(['approved_by_journalist', 'approved_by_spellchecker']);
    });
  });

  describe("getPlaces", () => {
    let node = (label, initial = false) => {
      return {
        id: () => label,
        data: (what) => what == 'label' ? label : initial,
      };
    };

    it("gets the simple places", () => {
      cytoscape.nodes['and'].returnValue([
        node('A'),
        node('B'),
      ]);
      let places = graph.getPlaces();
      expect(places.length).toBe(2);
      expect(places[0].label).toEqual('A');
      expect(places[1].label).toEqual('B');
    });

    it("returns initial place as the first one", () => {
      cytoscape.nodes['and'].returnValue([
        node('A'),
        node('B', true),
      ]);
      let places = graph.getPlaces();
      expect(places.length).toBe(2);
      expect(places[0].label).toEqual('B');
      expect(places[1].label).toEqual('A');
    });
  });
});
