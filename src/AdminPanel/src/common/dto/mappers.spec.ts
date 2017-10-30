import {ArrayMapper, Mapper, TypedMapMapper} from "./mappers";
import createSpyObj = jasmine.createSpyObj;
import Spy = jasmine.Spy;

describe(ArrayMapper.name, () => {
  it('maps from backend', done => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['fromBackendValue']);
    (itemMapper.fromBackendValue as Spy).and.returnValues(Promise.resolve(1), Promise.resolve(2), Promise.resolve(3));
    const arrayMapper = new ArrayMapper<any>(itemMapper, () => 'instance');
    arrayMapper.fromBackendValue(['a', 'b', 'c']).then(result => {
      expect(result).toEqual([1, 2, 3]);
    }).then(done);
    expect((itemMapper.fromBackendValue as Spy).calls.count()).toBe(3);
    expect((itemMapper.fromBackendValue as Spy).calls.first().args).toEqual(['a', 'instance']);
  });

  it('maps to backend', () => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['toBackendValue']);
    (itemMapper.toBackendValue as Spy).and.returnValues(1, 2, 3);
    const arrayMapper = new ArrayMapper<any>(itemMapper, undefined);
    const result = arrayMapper.toBackendValue(['a', 'b', 'c']);
    expect(result).toEqual([1, 2, 3]);
    expect((itemMapper.toBackendValue as Spy).calls.count()).toBe(3);
    expect((itemMapper.toBackendValue as Spy).calls.allArgs()).toEqual([['a'], ['b'], ['c']]);
  });

  it('clones', () => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['nullSafeClone']);
    (itemMapper.nullSafeClone as Spy).and.returnValues(1, 2, 3, 4, 5);
    const arrayMapper = new ArrayMapper<any>(itemMapper, undefined);
    let result = arrayMapper.nullSafeClone(['a', 'b', 'c']);
    expect(result).toEqual([1, 2, 3]);
    expect((itemMapper.nullSafeClone as Spy).calls.count()).toBe(3);
    expect((itemMapper.nullSafeClone as Spy).calls.allArgs()).toEqual([['a'], ['b'], ['c']]);
    result = arrayMapper.nullSafeClone(['x', 'y']);
    expect(result).toEqual([4, 5]);
    expect((itemMapper.nullSafeClone as Spy).calls.count()).toBe(5);
    expect((itemMapper.nullSafeClone as Spy).calls.allArgs()).toEqual([['a'], ['b'], ['c'], ['x'], ['y']]);
  });

  it('is null-safe', done => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['nullSafeClone']);
    const arrayMapper = new ArrayMapper<any>(itemMapper, undefined);
    // from backend
    const entity = {};
    arrayMapper.fromBackendProperty('xyz', {}, entity).then(() => {
      expect(entity['xyz']).toEqual(undefined);
      // to backend
      const dto = {};
      arrayMapper.toBackendProperty('abc', {}, dto);
      expect(dto['abc']).toEqual(undefined);
      // clone
      const clone = arrayMapper.nullSafeClone([(void 0) /* null */, undefined]);
      expect(clone).toEqual([(void 0), undefined]);
    }).then(done);
  });
});

describe(TypedMapMapper.name, () => {
  it('maps from backend', done => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['fromBackendValue']);
    (itemMapper.fromBackendValue as Spy).and.returnValues(Promise.resolve(1), Promise.resolve(2), Promise.resolve(3));
    const mapMapper = new TypedMapMapper<any>(itemMapper, () => 'instance');
    mapMapper.fromBackendValue({a: 11, b: 22, c: 33}).then(result => {
      expect(result).toEqual({a: 1, b: 2, c: 3});
    }).then(done);
    expect((itemMapper.fromBackendValue as Spy).calls.count()).toBe(3);
    expect((itemMapper.fromBackendValue as Spy).calls.first().args[1]).toEqual('instance');
  });

  it('maps to backend', () => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['toBackendValue']);
    (itemMapper.toBackendValue as Spy).and.returnValues(1, 2, 3);
    const mapMapper = new TypedMapMapper<any>(itemMapper, undefined);
    const result = mapMapper.toBackendValue({a: 11, b: 22, c: 33});
    expect(result).toEqual({a: 1, b: 2, c: 3});
    expect((itemMapper.toBackendValue as Spy).calls.count()).toBe(3);
  });

  it('clones', () => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['nullSafeClone']);
    (itemMapper.nullSafeClone as Spy).and.returnValues(1, 2, 3, 4, 5);
    const mapMapper = new TypedMapMapper<any>(itemMapper, undefined);
    let result = mapMapper.nullSafeClone({a: 11, b: 22, c: 33});
    expect(result).toEqual({a: 1, b: 2, c: 3});
    expect((itemMapper.nullSafeClone as Spy).calls.count()).toBe(3);
    expect((itemMapper.nullSafeClone as Spy).calls.allArgs()).toEqual([[11], [22], [33]]);
    result = mapMapper.nullSafeClone({x: 44, y: 55});
    expect(result).toEqual({x: 4, y: 5});
    expect((itemMapper.nullSafeClone as Spy).calls.count()).toBe(5);
    expect((itemMapper.nullSafeClone as Spy).calls.allArgs()).toEqual([[11], [22], [33], [44], [55]]);
  });

  it('is null-safe', done => {
    const itemMapper: Mapper<any> = createSpyObj('itemMapper', ['nullSafeClone']);
    const mapMapper = new TypedMapMapper<any>(itemMapper, undefined);
    // from backend
    const entity = {};
    mapMapper.fromBackendProperty('xyz', {}, entity).then(() => {
      expect(entity['xyz']).toEqual(undefined);
      // to backend
      const dto = {};
      mapMapper.toBackendProperty('abc', {}, dto);
      expect(dto['abc']).toEqual(undefined);
      // clone
      const clone = mapMapper.nullSafeClone({'null': (void 0), 'undef': undefined});
      expect(clone).toEqual(({'null': (void 0), 'undef': undefined}));
    }).then(done);
  });
});
