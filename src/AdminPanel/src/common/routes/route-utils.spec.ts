import {route} from "./route-utils";

describe('route-utils', () => {
  describe(route.name, () => {
    it("creates a route definition", () => {
      let def = route('url', 'module', 'title');
      expect(def.route).toEqual('url');
      expect(def.name).toEqual('module');
      expect(def.moduleId).toEqual('module');
      expect(def.title).toEqual('title');
      expect(def.settings).toBeDefined();
      expect(def.settings.icon).toBeUndefined();
    });

    it("creates route with icon if specified", () => {
      let def = route('url', 'module', 'title', {icon: 'icon'});
      expect(def.settings.icon).toEqual('icon');
    });
  });
});
