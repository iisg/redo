import {autoinject} from "aurelia-dependency-injection";
import {TypeRegistry} from "./registry";
import {Mapper} from "./mappers";

@autoinject
export class EntitySerializer {
  constructor(private typeRegistry: TypeRegistry) {
  }

  // We can't simply create deserialize(dto: Object) because we want to preserve methods and types of classes in returned object,
  // hence hydrateEntity which receives entity with all its bells and whistles and hydrates it with data, preserving that stuff.
  hydrate<E>(entity: E, dto: Object): Promise<E> {
    return this.getMapper(entity).fromBackendValue(dto, entity);
  }

  deserialize<E>(entityClassOrTypeName: string|Function, dto: Object): Promise<E> {
    const typeName = (typeof entityClassOrTypeName == 'string')
      ? entityClassOrTypeName as string
      : (entityClassOrTypeName as Function).name;
    const entity = this.typeRegistry.getEntityByType(typeName);
    return this.hydrate(entity, dto);
  }

  serialize(entity: any): Object {
    return this.getMapper(entity).toBackendValue(entity);
  }

  clone<E>(entity: E, type?: string): E {
    if (type === undefined) {
      type = entity.constructor.name;
    }
    return this.typeRegistry.getMapperByType(type).nullSafeClone(entity);
  }

  private getMapper<E>(entity: E|string): Mapper<E> {
    const typeName = (typeof entity === 'string') ? entity : entity.constructor.name;
    const mapper = this.typeRegistry.getMapperByType(typeName);
    if (mapper === undefined) {
      throw new MapperMissingError(typeName);
    }
    return mapper;
  }
}

class MapperMissingError extends Error {
  constructor(typeName: string) {
    super(`No mapper registered for type '${typeName}'. Did you forget to decorate it with @automapped or @copyable?`);
  }
}
