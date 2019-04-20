import {inject, Lazy} from "aurelia-dependency-injection";
import {Mapper} from "./mappers";
import {TypeRegistry} from "./registry";
import {getDtoProperties} from "./class-metadata-utils";
import {EntityClass, MapperClass} from "./contracts";

/**
 * Copies object using mapping instructions provided by @map and other property decorators. Non-decorated properties are omitted.
 */
@inject(Lazy.of(TypeRegistry))
export class AutoMapper<V> extends Mapper<V> {
  constructor(private typeRegistry: () => TypeRegistry) {
    super();
  }

  fromBackendValue(dto: any, entity: V): Promise<V> {
    const typeProperties = getDtoProperties(entity);
    const promises = [];
    for (const propertyName in typeProperties) {
      const handlingInstruction = typeProperties[propertyName];
      const mapper = this.typeRegistry().getMapper(handlingInstruction);
      if (mapper === undefined) {
        const typeName = (handlingInstruction as MapperClass<any>).name || handlingInstruction as string;
        throw new Error(`Mapper not found for type '${typeName}'`);
      }
      const promise = mapper.fromBackendProperty(propertyName, dto, entity)
        .then(value => entity[propertyName] = value);
      promises.push(promise);
    }
    return Promise.all(promises).then(() => entity);
  }

  toBackendValue(entity: V): Object {
    const dto = {};
    const typeProperties = getDtoProperties(entity);
    for (const propertyName in typeProperties) {
      const handlingInstruction = typeProperties[propertyName];
      const mapper = this.typeRegistry().getMapper(handlingInstruction);
      if (mapper === undefined) {
        const typeName = (handlingInstruction as MapperClass<any>).name || handlingInstruction as string;
        throw new Error(`Mapper not found for type '${typeName}'`);
      }
      mapper.toBackendProperty(propertyName, entity, dto);
    }
    return dto;
  }

  protected clone(source: V): V {
    const typeProperties = getDtoProperties(source);
    const target = this.typeRegistry().getEntityByType((source.constructor as EntityClass<any>).NAME);
    for (const propertyName in typeProperties) {
      const handlingInstruction = typeProperties[propertyName];
      const mapper = this.typeRegistry().getMapper(handlingInstruction);
      target[propertyName] = mapper.nullSafeClone(source[propertyName]);
    }
    return target;
  }
}

export class AutoMapperWithCustomProperties<V> extends AutoMapper<V> {
  toBackendValue(entity: V): Object {
    return this.addCustomProperties(entity, super.toBackendValue(entity) as any);
  }

  fromBackendValue(dto: any, entity: V): Promise<V> {
    return super.fromBackendValue(dto, entity).then(mapped => this.addCustomProperties(dto, mapped));
  }

  protected clone(source: V): V {
    return this.addCustomProperties(source, super.clone(source));
  }

  private addCustomProperties<E>(source: any, target: E): E {
    for (const prop in source) {
      if (!target.hasOwnProperty(prop)) {
        target[prop] = source[prop];
      }
    }
    return target;
  }
}
