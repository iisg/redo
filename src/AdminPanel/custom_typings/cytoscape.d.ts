// There are some unofficial typings for cytoscape, but they are not published "bo nie" and buggy as hell.
// Here is minimalistic version that allows to work with cytoscape in TS.
// https://github.com/cytoscape/cytoscape.js/issues/1012
// https://github.com/phreed/ts-typings/blob/cytoscape/index.d.ts
declare namespace Cytoscape {
  interface Static {
    (options?): Instance;
    stylesheet(): any;
  }

  interface Instance {
    add(elements: any): any;
    remove(elements: any): any;
    contextMenus(options): any;
    edgehandles(options): any;
    $(selector): any;
    fit(eles: any, padding: number): void;
    layout(layout): void;
    on(event: string, selectorOrCallback: ((e) => any)|string, callback?: (e) => any): void;
    elements(): any;
    autopanOnDrag(): void;
    animate(param: any): void;
    json(): any;
    nodes(): any;
    png(options: any): any;
    edges(): any;
    destroy(): void;
    resize(): void;
    autoungrabify(ungrabify?: boolean): boolean;
    autounselectify(autounselectify?: boolean): boolean;
  }
}

declare var cytoscape: Cytoscape.Static;

declare module "cytoscape" {
  export = cytoscape;
}

declare namespace CytoscapeContextMenus {
  interface Static {
    (cy: Cytoscape.Static, jquery: JQueryStatic);
  }
}

declare var cytoscapeContextMenus: CytoscapeContextMenus.Static;

declare module "cytoscape-context-menus" {
  export = cytoscapeContextMenus;
}

declare namespace CytoscapeEdgeHandles {
  interface Static {
    (cy: Cytoscape.Static);
  }
}

declare var cytoscapeEdgeHandles: CytoscapeEdgeHandles.Static;

declare module "cytoscape-edgehandles" {
  export = cytoscapeEdgeHandles;
}

declare namespace AutopanOnDrag {
  interface Static {
    (cy: Cytoscape.Static);
  }
}

declare var autopanOnDrag: AutopanOnDrag.Static;

declare module "cytoscape-autopan-on-drag" {
  export = autopanOnDrag;
}
