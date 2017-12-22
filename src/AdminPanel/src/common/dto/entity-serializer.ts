import {autoinject} from "aurelia-dependency-injection";
import {TypeRegistry} from "./registry";
import {Mapper} from "./mappers";
import {EntityClass} from "./contracts";

@autoinject
export class EntitySerializer {
  constructor(private typeRegistry: TypeRegistry) {
  }

  // We can't simply create deserialize(dto: Object) because we want to preserve methods and types of classes in returned object,
  // hence hydrateEntity which receives entity with all its bells and whistles and hydrates it with data, preserving that stuff.
  hydrate<E>(entity: E, dto: Object, mapper?: Mapper<E>): Promise<E> {
    return (mapper || this.getMapper(entity)).fromBackendValue(dto, entity);
  }

  deserialize<E>(entityClassOrTypeName: string | EntityClass<any>, dto: Object): Promise<E> {
    const typeName = (typeof entityClassOrTypeName == 'string')
      ? entityClassOrTypeName as string
      : (entityClassOrTypeName as EntityClass<any>).NAME;
    const entity = this.typeRegistry.getEntityByType(typeName);
    const mapper = this.getMapper(typeName);
    return this.hydrate(entity, dto, mapper);
  }

  serialize(entity: any): Object {
    return this.getMapper(entity).toBackendValue(entity);
  }

  clone<E>(entity: E, type?: string): E {
    if (type === undefined) {
      type = (entity.constructor as EntityClass<E>).NAME;
    }
    return this.typeRegistry.getMapperByType(type).nullSafeClone(entity);
  }

  hydrateClone<T>(source: T, target: T): T {
    const sourceType = (source.constructor as EntityClass<T>).NAME;
    const targetType = (target.constructor as EntityClass<T>).NAME;
    if (!sourceType || sourceType != targetType) {
      throw new Error('Both objects must be instances of the same entity class');
    }
    const sourceClone = this.clone(source);
    for (const propertyName of Object.keys(sourceClone)) {  // unlike for(key in obj) loops, this won't iterate over getters/setters
      target[propertyName] = sourceClone[propertyName];
    }
    return target;
  }

  private getMapper<E>(entity: E | string): Mapper<E> {
    const typeName = (typeof entity === 'string') ? entity : (entity.constructor as EntityClass<E>).NAME;
    const mapper = this.typeRegistry.getMapperByType(typeName);
    if (mapper === undefined) {
      throw new MapperMissingError(typeName);
    }
    return mapper;
  }
}

class MapperMissingError extends Error {
  constructor(typeName: string) {
    super(`No mapper registered for type '${typeName}'. Did you forget to decorate it with @mappedWith or @automapped?`);
  }
}
