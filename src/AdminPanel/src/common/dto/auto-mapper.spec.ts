import {AutoMapper} from "./auto-mapper";
import {map} from "./decorators";
import {CopyMapper, Mapper} from "./mappers";
import {TypeRegistry} from "./registry";
import createSpyObj = jasmine.createSpyObj;
import Spy = jasmine.Spy;
import createSpy = jasmine.createSpy;

describe(AutoMapper.name, () => {
  class TestEntity {
    @map num: number;
    @map('Foo') foo: Object;
    @map(CopyMapper) bar: Object;
  }
  let mapper: Mapper<any>;
  let registry: TypeRegistry;

  beforeEach(() => {
    mapper = createSpyObj('mapper', ['fromBackendProperty', 'toBackendProperty', 'nullSafeClone']);
    (mapper.fromBackendProperty as Spy).and.returnValues(Promise.resolve('from1'), Promise.resolve('from2'), Promise.resolve('from3'));
    registry = createSpyObj('registry', ['getMapper']) as TypeRegistry;
    (registry.getMapper as Spy).and.returnValue(mapper);
  });

  it('maps from backend', done => {
    const entity = new TestEntity();
    const automapper = new AutoMapper(() => registry);
    automapper.fromBackendValue({}, entity).then(() => {
      expect(entity.num).toEqual('from1');
      expect(entity.foo).toEqual('from2');
      expect(entity.bar).toEqual('from3');
      expect((registry.getMapper as Spy).calls.allArgs()).toEqual([['Number'], ['Foo'], [CopyMapper]]);
      expect((mapper.fromBackendProperty as Spy).calls.allArgs()).toEqual([['num', {}, entity], ['foo', {}, entity], ['bar', {}, entity]]);
    }).then(done);
  });

  it('maps to backend', () => {
    const entity = new TestEntity();
    const automapper = new AutoMapper(() => registry);
    automapper.toBackendValue(entity);  // result is ignored because it's produced entirely by field mappers which are mocked anyway
    expect((registry.getMapper as Spy).calls.allArgs()).toEqual([['Number'], ['Foo'], [CopyMapper]]);
    expect((mapper.toBackendProperty as Spy).calls.allArgs()).toEqual([['num', entity, {}], ['foo', entity, {}], ['bar', entity, {}]]);
  });

  it('clones', () => {
    registry.getEntityByType = createSpy('instantiateByType').and.returnValue(new TestEntity());
    const entity = $.extend(new TestEntity(), {num: 1, foo: 2, bar: 3});
    const automapper = new AutoMapper(() => registry);
    automapper.nullSafeClone(entity);  // result is ignored because it's produced entirely by field mappers which are mocked anyway
    expect((registry.getMapper as Spy).calls.allArgs()).toEqual([['Number'], ['Foo'], [CopyMapper]]);
    expect((registry.getEntityByType as Spy).calls.count()).toEqual(1);
    expect((mapper.nullSafeClone as Spy).calls.allArgs()).toEqual([[1], [2], [3]]);
  });
});
