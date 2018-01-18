import {deepCopy, zip} from "../utils/object-utils";
import {FactoryFunction} from "./registry";

/**
 * This is a base class for all mappers. It's capable of doing three things:
 *
 * 1. Converting objects from backend format to entity format (promise-based)
 * 2. Converting objects from entity format to backend format
 * 3. Cloning entities
 *
 * Cloning (#3) should be implemented in the clone() method. It receives an entity and should return another one (not a promise, so async
 * operations are not allowed). clone() is protected and guaranteed to never receive `undefined` or `null`, this case is handled in public
 * method called `nullSafeClone`.
 *
 * fromBackendValue() receives a backend value and should return a promise that resolves to entity value.
 * toBackendValue() receives an entity value and should return a backend value (synchronously).
 * Both methods can assume that `undefined` won't be passed to them, it should be handled by caller.
 *
 * Default implementation of mappers assumes that each field in DTO corresponds to the same field in entity. If this is not the case,
 * AdvancedMapper should be extended instead which will reject calls of from/toBackendValue.
 */
export abstract class Mapper<V> {
  protected isEmpty(value: any): boolean {
    return (value === undefined) || (value === null);  // tslint:disable-line
  }

  fromBackendProperty(key: string, dto: Object, entity: Object): Promise<V> {
    return this.isEmpty(dto[key])
      ? Promise.resolve(entity[key])
      : this.fromBackendValue(dto[key], entity[key]);
  }

  toBackendProperty(key: string, entity: Object, dto: Object): void {
    if (this.isEmpty(entity[key])) {
      return;
    }
    dto[key] = this.toBackendValue(entity[key]);
  }

  nullSafeClone(entity: V): V {
    return this.isEmpty(entity) ? undefined : this.clone(entity);
  }

  abstract fromBackendValue(dto: any, currentEntity: V): Promise<V>;

  abstract toBackendValue(entity: V): any;

  protected abstract clone(entity: V): V;
}

/**
 * This class is useful in cases where:
 * 1. Field names don't correspond to each other in DTO and entity (eg. entity.kind corresponds to dto.kindId)
 * 2. A field in entity corresponds to multiple fields in DTO
 *
 * from/toBackendProperty aren't guarded against undefined and null values, these have to be handled in implementations.
 * this.isEmpty(value) is useful for checking for nullity/undefinedness.
 */
export abstract class AdvancedMapper<V> extends Mapper<V> {
  private readonly ERROR_MESSAGE = "This method can't be called on advanced mappers";

  abstract fromBackendProperty(key: string, dto: Object, entity: Object): Promise<V>;

  abstract toBackendProperty(key: string, entity: Object, dto: Object): void;

  fromBackendValue(): Promise<V> {
    throw new Error(this.ERROR_MESSAGE);
  }

  toBackendValue(): any {
    throw new Error(this.ERROR_MESSAGE);
  }
}

/**
 * Returns values as-is. This is fine for primitives, but very bad idea for objects.
 */
export class IdentityMapper<V> extends Mapper<V> {
  fromBackendValue(dto: V): Promise<V> {
    return Promise.resolve(dto);
  }

  toBackendValue(entityValue: V): V {
    return entityValue;
  }

  protected clone(entity: V): V {
    return entity;
  }
}

/**
 * Just deep-copies values both ways. It will strip all methods from objects - use with care!
 */
export class CopyMapper<V> extends Mapper<V> {
  fromBackendValue(dto: V): Promise<V> {
    return Promise.resolve(deepCopy(dto));
  }

  toBackendValue(value: V): any {
    return deepCopy(value);
  }

  protected clone(entity: V): V {
    return deepCopy(entity);
  }
}

/**
 * Maps arrays using another mapper (provided in constructor) for each item.
 */
export class ArrayMapper<I> extends Mapper<I[]> {
  constructor(private itemMapper: Mapper<I>, private itemFactory: FactoryFunction<I>) {
    super();
  }

  fromBackendValue(dtoItems: any[]): Promise<I[]> {
    const arrayOfPromises = dtoItems.map(
      item => this.isEmpty(item) ? item : this.itemMapper.fromBackendValue(item, this.itemFactory())
    );
    return Promise.all(arrayOfPromises);
  }

  toBackendValue(items: I[]): any[] {
    return items.map(item => this.isEmpty(item) ? item : this.itemMapper.toBackendValue(item));
  }

  protected clone(entity: I[]): I[] {
    return entity.map(item => this.isEmpty(item) ? item : this.itemMapper.nullSafeClone(item));
  }
}

/**
 * Maps maps (ie. dictionaries) using another mapper (provided in constructor) for each value, preserving keys.
 */
export class TypedMapMapper<V> extends Mapper<AnyMap<V>> {
  constructor(private itemMapper: Mapper<V>, private itemFactory: FactoryFunction<V>) {
    super();
  }

  fromBackendValue(dtoMap: AnyMap<any>): Promise<AnyMap<V>> {
    const keys = Object.keys(dtoMap);
    const originalValues = keys.map(key => dtoMap[key]);
    const valuePromises = originalValues.map(
      item => this.isEmpty(item) ? item : this.itemMapper.fromBackendValue(item, this.itemFactory())
    );
    return Promise.all(valuePromises).then(values => zip(keys, values));
  }

  toBackendValue(entityMap: AnyMap<V>): AnyMap<any> {
    const keys = Object.keys(entityMap);
    const originalValues = keys.map(key => entityMap[key]);
    const mappedValues = originalValues.map(item => this.isEmpty(item) ? item : this.itemMapper.toBackendValue(item));
    return zip(keys, mappedValues);
  }

  protected clone(map: AnyMap<V>): AnyMap<V> {
    const keys = Object.keys(map);
    const originalValues = keys.map(key => map[key]);
    const mappedValues = originalValues.map(item => this.isEmpty(item) ? item : this.itemMapper.nullSafeClone(item));
    return zip(keys, mappedValues);
  }
}
