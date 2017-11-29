import {metadata} from "aurelia-metadata";
import {Class, EntityClass, MapperClass} from "./contracts";

const propertiesMetadata = 'dto_properties';

type HandlingInstruction<T> = string | MapperClass<T>;
type DtoProperties = StringMap<HandlingInstruction<any>>;

export function getDtoProperties(entityOrClass: Class<any> | Object): DtoProperties {
  const klass = toClass(entityOrClass);
  return metadata.get(propertiesMetadata, klass as Function) as any || {};
}

export function addDtoProperty<T>(entityOrClass: Class<T> | T, propertyName: string, handlingInstruction: HandlingInstruction<T>): void {
  const klass = toClass(entityOrClass);
  const properties = getDtoProperties(klass);
  properties[propertyName] = handlingInstruction;
  metadata.define(propertiesMetadata, properties, klass as Function);
}

export function inferHandlingInstruction(entityOrClass: EntityClass<any> | Object, propertyName: string): string {
  const entityClass = toClass<any, EntityClass<any>>(entityOrClass);
  const entityPrototype = entityClass.prototype;
  const inferredType: EntityClass<any> | Class<any> = metadata.getOwn('design:type', entityPrototype, propertyName) as any;
  const className = entityClass.NAME || entityClass.name;
  const userFriendlyRef = className + '.' + propertyName;
  if (inferredType === undefined) {
    throw new Error(
      `TypeScript didn't emit type metadata for this property. Provide explicit type in decorator for ${userFriendlyRef} as a workaround.`
    );
  } else if (inferredType.name == 'Array') {
    throw new Error(`Array member types can't be autodetected. Annotate type explicitly for ${userFriendlyRef}`);
  }
  return (inferredType as EntityClass<any>).NAME || inferredType.name;
}

function toClass<T, C extends Class<T>>(objectOrClass: C | T): C {
  return ('prototype' in objectOrClass) ? objectOrClass as C : objectOrClass.constructor as C;
}
