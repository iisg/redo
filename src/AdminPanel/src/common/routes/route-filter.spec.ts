import {RouteFilter} from "./route-filter";
import {NavRole, Route} from "./routing-builder";

describe(RouteFilter.name, () => {
  let top1: Route, top2: Route, bottom1: Route, bottom2: Route;
  let classAgnostic1: Route, classAgnostic2: Route, classFoo: Route, classBar: Route;
  let secondaryClassAgnostic1: Route, secondaryClassAgnostic2: Route, secondaryClassFoo: Route, secondaryClassBar: Route;
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
        classFoo,
        secondaryClassFoo,
        secondaryClassAgnostic1,
        secondaryClassBar,
        classAgnostic2,
        secondaryClassAgnostic2,
        classBar,
        bottom2
      ]) as any;
    },
  };

  function route(role: NavRole, className?: string): Route {
    return new Route(Math.random() + '', '', '').withMenuItem('', role, undefined, className);
  }

  beforeEach(() => {
    top1 = route(NavRole.TOP);
    top2 = route(NavRole.TOP);
    bottom1 = route(NavRole.BOTTOM);
    bottom2 = route(NavRole.BOTTOM);
    classAgnostic1 = route(NavRole.PER_RESOURCE_CLASS);
    classAgnostic2 = route(NavRole.PER_RESOURCE_CLASS);
    classFoo = route(NavRole.PER_RESOURCE_CLASS, 'foo');
    classBar = route(NavRole.PER_RESOURCE_CLASS, 'bar');
    secondaryClassAgnostic1 = route(NavRole.PER_RESOURCE_CLASS_SECONDARY);
    secondaryClassAgnostic2 = route(NavRole.PER_RESOURCE_CLASS_SECONDARY);
    secondaryClassFoo = route(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'foo');
    secondaryClassBar = route(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'bar');
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

  it('rejects invalid arguments', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(() => filter.getRoutes(NavRole.TOP, 'whatever')).toThrow();
    expect(() => filter.getRoutes(NavRole.BOTTOM, 'whatever')).toThrow();
    expect(() => filter.getRoutes(NavRole.PER_RESOURCE_CLASS)).toThrow();
    expect(() => filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY)).toThrow();
  });

  it('filters PER_RESOURCE_CLASS routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS, 'whatever')).toEqual([classAgnostic1, classAgnostic2]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS, 'whatever2')).toEqual([classAgnostic1, classAgnostic2]);
  });

  it('filters PER_RESOURCE_CLASS_SECONDARY routes', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'whatever')).toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'whatever2')).toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2]);
  });

  it('class-specific PER_RESOURCE_CLASS routes override class-agnostic', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS, 'foo')).toEqual([classFoo]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS, 'bar')).toEqual([classBar]);
  });

  it('class-specific PER_RESOURCE_CLASS_SECONDARY routes are appended to class-agnostic', () => {
    const filter = new RouteFilter(routeProviderMock);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'foo'))
      .toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2, secondaryClassFoo]);
    expect(filter.getRoutes(NavRole.PER_RESOURCE_CLASS_SECONDARY, 'bar'))
      .toEqual([secondaryClassAgnostic1, secondaryClassAgnostic2, secondaryClassBar]);
  });
});
