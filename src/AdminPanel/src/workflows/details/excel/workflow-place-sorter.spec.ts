import {Workflow, WorkflowPlace, WorkflowTransition} from "../../workflow";
import {WorkflowPlaceSorter} from "./workflow-place-sorter";

describe('workflow-place-sorter', () => {
  function createWorkflowMock(places: string[], transitions: StringMap<string[]>): Workflow {
    const placeObjects: WorkflowPlace[] = places.map(id => {
      return {id, label: {}, restrictingMetadataIds: {}};
    });
    const transitionObjects: WorkflowTransition[] = Object.keys(transitions).map(from => {
      const tos = transitions[from];
      return {id: `_${from}_`, label: {}, froms: [from], tos};
    });
    const id: number = Math.floor(Math.random() * 10000);
    return $.extend(new Workflow(), {id, name: {}, enabled: true, places: placeObjects, transitions: transitionObjects});
  }

  let sorter: WorkflowPlaceSorter;
  beforeEach(() => {
    sorter = new WorkflowPlaceSorter();
  });

  it('returns linear workflows unchanged', () => {
    const workflow = createWorkflowMock(['a', 'b', 'c'], {'a': ['b'], 'b': ['c']});
    const result = sorter.getOrderedPlaces(workflow);
    const resultIds = result.map(place => place.id);
    expect(resultIds).toEqual(['a', 'b', 'c']);
  });

  it('sorts complex workflows', () => {
    // @formatter:off
    /*        c
             //
             b - d
            /     \
        -> a - e - f - g     */
    // @formatter:on
    const workflow = createWorkflowMock(
      ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
      {'a': ['b', 'e'], 'b': ['c', 'd'], 'c': ['b'], 'd': ['f'], 'e': ['f'], 'f': ['g']}
    );
    const result = sorter.getOrderedPlaces(workflow);
    const resultIds = result.map(place => place.id);
    expect(resultIds).toEqual(['a', 'b', 'e', 'c', 'd', 'f', 'g']);
  });

  it('includes loose nodes', () => {
    const workflow = createWorkflowMock(['a', 'b', 'c'], {'a': ['b']});
    const result = sorter.getOrderedPlaces(workflow);
    const resultIds = result.map(place => place.id);
    expect(resultIds).toEqual(['a', 'b', 'c']);
  });

  it('handles no places in graph', () => {
    const workflow = createWorkflowMock([], {});
    const result = sorter.getOrderedPlaces(workflow);
    const resultIds = result.map(place => place.id);
    expect(resultIds).toEqual([]);
  });
});
