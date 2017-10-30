import {metadata} from "aurelia-metadata";

const propertiesMetadata = 'dto_properties';

type HandlingInstruction = string|Function;
type DtoProperties = StringMap<HandlingInstruction>;
type EntityType = Function|Object;

export function getDtoProperties(type: EntityType): DtoProperties {
  const constructor = toConstructor(type);
  return metadata.get(propertiesMetadata, constructor) as any || {};
}

export function addDtoProperty(type: EntityType, propertyName: string, handlingInstruction: HandlingInstruction): void {
  const constructor = toConstructor(type);
  const properties = getDtoProperties(type);
  properties[propertyName] = handlingInstruction;
  metadata.define(propertiesMetadata, properties, constructor);
}

function toConstructor(type: EntityType): Function {
  return type.constructor || type as Function;
}

export function inferHandlingInstruction(entityType: Function, propertyName: string): string {
  const type: Function = metadata.getOwn('design:type', entityType, propertyName) as Function;
  const className = (entityType as Function).name || entityType.constructor.name;
  const userFriendlyRef = className + '.' + propertyName;
  if (type === undefined) {
    throw new Error(
      `TypeScript didn't emit type metadata for this property. Provide explicit type in decorator for ${userFriendlyRef} as a workaround.`
    );
  } else if (type.name == 'Array') {
    throw new Error(`Array member types can't be autodetected. Annotate type explicitly for ${userFriendlyRef}`);
  }
  return type.name;
}
