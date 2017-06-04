export function workflowGraphDefaultStylesheet() {
  return [
    {
      selector: 'node',
      style: {
        'label': 'data(labelToDisplay)',
        'text-valign': 'bottom',
        'color': '#0c395c',
        'background-color': '#2196f3'
      }
    },
    {
      selector: 'edge',
      style: {
        'width': 2,
        'background': '#FFF',
        'label': 'data(labelToDisplay)',
        'line-color': '#62b5f7',
        'curve-style': 'bezier',
        'text-outline-width': 2,
        'text-outline-color': '#EEE',
        'target-arrow-color': '#62b5f7',
        'target-arrow-shape': 'triangle',
        'edge-text-rotation': 'autorotate'
      }
    },
    {
      selector: ':selected',
      style: {
        'color': 'white',
        'background-color': '#0e2e48',
        'line-color': 'black',
        'target-arrow-color': 'black',
        'source-arrow-color': 'black',
        'text-outline-color': 'black',
        'text-outline-width': 2
      }
    },
    {
      selector: '.initial',
      style: {
        'border-color': '#0e2e48',
        'border-width': '3',
        'border-style': 'solid'
      }
    },
    {
      selector: '.current',
      style: {
        'background-color': '#8bc34a'
      }
    }
  ];
}
