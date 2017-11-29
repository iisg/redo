import {TypeRegistry} from "./registry";
import {Container} from "aurelia-dependency-injection";
import {MapperClass} from "./contracts";
import createSpyObj = jasmine.createSpyObj;
import Spy = jasmine.Spy;
import createSpy = jasmine.createSpy;

describe(TypeRegistry.name, () => {
  it('returns appropriate mapper for type', () => {
    const container: Container = createSpyObj('container', ['get']);
    (container.get as Spy).and.returnValue('mapper instance');
    const registry = new TypeRegistry(container);
    const specificMapper: MapperClass<any> = 'specific' as any;
    const arrayItemMapper: MapperClass<any> = 'array' as any;
    const mapValueMapper: MapperClass<any> = 'map' as any;
    registry.register('specific1[]', specificMapper);
    registry.register('{specific2}', specificMapper);
    registry.register('arrayItem', arrayItemMapper);
    registry.register('mapValue', mapValueMapper);
    registry.getMapperByType('specific1[]');
    registry.getMapperByType('{specific2}');
    expect((container.get as Spy).calls.allArgs()).toEqual([[specificMapper], [specificMapper]]);
    expect(registry.getMapperByType('arrayItem[]').constructor.name).toBe('ArrayMapper');
    expect(registry.getMapperByType('{mapValue}').constructor.name).toBe('TypedMapMapper');
    expect(registry.getMapperByType('unknown')).toBe(undefined);
  });

  it('returns instance of mapper', () => {
    const container: Container = createSpyObj('container', ['get']);
    (container.get as Spy).and.returnValues('mapper1', 'mapper2');
    const registry = new TypeRegistry(container);
    const mapperClass1 = function () {
    } as any;
    const mapperClass2 = function () {
    } as any;
    let mapper = registry.getMapper(mapperClass1);
    expect((container.get as Spy).calls.allArgs()).toEqual([[mapperClass1]]);
    expect(mapper).toEqual('mapper1');
    registry.register('qwerty', mapperClass2);
    mapper = registry.getMapper('qwerty');
    expect((container.get as Spy).calls.allArgs()).toEqual([[mapperClass1], [mapperClass2]]);
    expect(mapper).toEqual('mapper2');
  });

  it('instantiates entities using factories', () => {
    const registry = new TypeRegistry(undefined);
    expect(() => registry.getEntityByType('qwerty')).toThrow();
    const factory = createSpy('factory').and.returnValue('instance');
    registry.register('qwerty', undefined, factory);
    expect(registry.getEntityByType('qwerty')).toEqual('instance');
    expect(factory.calls.count()).toBe(1);
  });
});
