import {NestedNavigationValueConverter} from "./nested-navigation-value-converter";
import {EventAggregator} from "aurelia-event-aggregator";
import {route, nested, flatten} from "./route-utils";
import {NavItemWithChildren} from "./route-types";

describe(NestedNavigationValueConverter.name, () => {
  let eventAggregator: EventAggregator;
  let valueConverter: NestedNavigationValueConverter;

  beforeEach(() => {
    eventAggregator = new EventAggregator();
    valueConverter = new NestedNavigationValueConverter(eventAggregator);
  });

  it("does not change routes if none of them are nested", () => {
    const routes = [
      route('url1', 'module1', 'title1'),
      route('url2', 'module2', 'title2'),
    ];
    const converted = valueConverter.toView(routes);
    expect(converted).toEqual(routes);
  });

  it("nests children", () => {
    const routes = flatten([
      route('notnested', 'nn', 'nn'),
      nested('nested', 'nested', [
        route('url1', 'module1', 'title1'),
        route('url2', 'module2', 'title2'),
      ]),
    ]);
    const converted = valueConverter.toView(routes);
    expect(converted.length).toEqual(2);
    expect(converted[0].route).toEqual('notnested');
    expect(converted[1].route).toBeUndefined();
    const nestedDef = converted[1] as NavItemWithChildren;
    expect(nestedDef.children.length).toBe(2);
    expect(nestedDef.children.map(item => item.route)).toEqual(['url1', 'url2']);
  });

  it("expands the nested route when the route changes and any of the children is active", () => {
    const routes = nested('nested', 'nested', [
      route('url1', 'module1', 'title1'),
      route('url2', 'module2', 'title2'),
    ]);
    const converted = valueConverter.toView(routes);
    const nestedDef = converted[0] as NavItemWithChildren;
    expect(nestedDef.expanded).toBeFalsy();
    nestedDef.children[1].isActive = true;
    eventAggregator.publish('router:navigation:complete');
    expect(nestedDef.expanded).toBeTruthy();
  });

  it("collapses the nested route when the route changes and none of the children are active", () => {
    const routes = nested('nested', 'nested', [
      route('url1', 'module1', 'title1'),
      route('url2', 'module2', 'title2'),
    ]);
    const converted = valueConverter.toView(routes);
    const nestedDef = converted[0] as NavItemWithChildren;
    nestedDef.toggle();
    eventAggregator.publish('router:navigation:complete');
    expect(nestedDef.expanded).toBeFalsy();
  });

  it("collapses the other nested route if other is expanded", () => {
    const routes = flatten([
      nested('nested1', 'nested1', [
        route('url1', 'module1', 'title1'),
        route('url2', 'module2', 'title2'),
      ]),
      nested('nested2', 'nested2', [
        route('url3', 'module3', 'title3'),
        route('url4', 'module4', 'title4'),
      ])
    ]);
    const converted = valueConverter.toView(routes);
    const nestedDef1 = converted[0] as NavItemWithChildren;
    const nestedDef2 = converted[1] as NavItemWithChildren;
    expect(nestedDef1.expanded).toBeFalsy();
    expect(nestedDef2.expanded).toBeFalsy();
    nestedDef1.toggle();
    expect(nestedDef1.expanded).toBeTruthy();
    expect(nestedDef2.expanded).toBeFalsy();
    nestedDef2.toggle();
    expect(nestedDef1.expanded).toBeFalsy();
    expect(nestedDef2.expanded).toBeTruthy();
    nestedDef2.toggle();
    expect(nestedDef1.expanded).toBeFalsy();
    expect(nestedDef2.expanded).toBeFalsy();
  });

  it("does not collapse the nested route if any of the child is active", () => {
    const routes = flatten([
      nested('nested1', 'nested1', [
        route('url1', 'module1', 'title1'),
        route('url2', 'module2', 'title2'),
      ]),
      nested('nested2', 'nested2', [
        route('url3', 'module3', 'title3'),
        route('url4', 'module4', 'title4'),
      ])
    ]);
    const converted = valueConverter.toView(routes);
    const nestedDef1 = converted[0] as NavItemWithChildren;
    const nestedDef2 = converted[1] as NavItemWithChildren;
    nestedDef1.toggle();
    nestedDef1.children[0].isActive = true;
    nestedDef2.toggle();
    expect(nestedDef1.expanded).toBeTruthy();
    expect(nestedDef2.expanded).toBeTruthy();
  });
});
