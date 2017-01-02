import {route, nested, flatten} from "./route-utils";

describe('route-utils', () => {
  describe(route.name, () => {
    it("creates a route definition", () => {
      let def = route('url', 'module', 'title');
      expect(def.route).toEqual('url');
      expect(def.name).toEqual('module');
      expect(def.moduleId).toEqual('module');
      expect(def.nav).toBeTruthy();
      expect(def.title).toEqual('title');
      expect(def.settings).toBeDefined();
      expect(def.settings.icon).toBeUndefined();
    });

    it("creates route with icon if specified", () => {
      let def = route('url', 'module', 'title', {icon: 'icon'});
      expect(def.settings.icon).toEqual('icon');
    });
  });

  describe(nested.name, () => {
    it("creates a nested route definition", () => {
      let def = nested('parent title', 'parent icon', [
        route('url1', 'module1', 'title1'),
        route('url2', 'module2', 'title2'),
      ]);
      expect(def.length).toEqual(2);
      expect(def[0].route).toEqual('url1');
      expect(def[1].route).toEqual('url2');
      expect(def[0].settings.parentIcon).toEqual('parent icon');
      expect(def[1].settings.parentIcon).toEqual('parent icon');
      expect(def[0].settings.parentTitle).toEqual('parent title');
      expect(def[1].settings.parentTitle).toEqual('parent title');
    });
  });

  describe(flatten.name, () => {
    it("flattens", () => {
      const def = [
        route('url', 'module', 'title'),
        nested('parent title', 'parent icon', [
          route('url1', 'module1', 'title1'),
          route('url2', 'module2', 'title2'),
        ])
      ];
      const flattened = flatten(def);
      expect(flattened.length).toBe(3);
      expect(flattened.map(r => r.route)).toEqual(['url', 'url1', 'url2']);
    });
  });
});
