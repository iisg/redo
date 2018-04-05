import {RouteFilter} from "./route-filter";
import {NavRole, Route} from "./routing-builder";

describe(RouteFilter.name, () => {
  let top1: Route, top2: Route, bottom1: Route, bottom2: Route;
  let classAgnostic1: Route, classAgnostic2: Route;
  let secondaryClassAgnostic1: Route, secondaryClassAgnostic2: Route;
  const routeProviderMock = {
    getRoutes: () => {
      throw new Error('Mock not configured');
    },
    configure: () => {
      routeProviderMock.getRoutes = (() => [
        top1,
        bottom1,
        classAgnostic1,
        top2,
        secondaryClassAgnostic1,
        classAgnostic2,
        secondaryClassAgnostic2,
        bottom2
      ]) as any;
    },
  };

  function route(role: NavRole): Route {
    return new Route(Math.random() + '', '', '').withMenuItem('', role, undefined);
  }

  beforeEach(() => {
    top1 = route(NavRole.TOP);
    top2 = route(NavRole.TOP);
    bottom1 = route(NavRole.BOTTOM);
    bottom2 = route(NavRole.BOTTOM);
    classAgnostic1 = route(NavRole.PER_RESOURCE_CLASS);
    classAgnostic2 = route(NavRole.PER_RESOURCE_CLASS);
    secondaryClassAgnostic1 = route(NavRole.PER_RESOURCE_CLASS_SECONDARY);
    secondaryClassAgnostic2 = route(NavRole.PER_RESOURCE_CLASS_SECONDARY);
    routeProviderMock.configure();
  });

  it('filters TOP routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.TOP)).toEqual([top1, top2]);
  });

  it('filters BOTTOM routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.BOTTOM)).toEqual([bottom1, bottom2]);
  });

  it('filters PER_RESOURCE_CLASS routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS)).toEqual([classAgnostic1, classAgnostic2]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS)).toEqual([classAgnostic1, classAgnostic2]);
  });

  it('filters PER_RESOURCE_CLASS_SECONDARY routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY)).toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY)).toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2]);
  });
});
