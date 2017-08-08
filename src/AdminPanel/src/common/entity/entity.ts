let strippableEntityProperties = [];

export abstract class Entity {
  @strippable hovered: boolean = false;
  @strippable editing: boolean = false;
  @strippable pendingRequest: boolean = false;

  static stripFrontendProperties(entity: Object) {
    for (const propertyName of strippableEntityProperties) {
      delete entity[propertyName];
    }
  }
}

function strippable(prototype: any, propertyName: string) {
  strippableEntityProperties.push(propertyName);
}

/*
 Don't convert these functions into methods!
 Methods assign class fields directly and don't fire Aurelia's bindings.
 Functions use public access bindings and cause bindings to update properly.
 Usage:
 promise
 .then(setPendingRequest(entity)
 .then(foo)
 .finally(clearPendingRequest(entity)
 */

export function setPendingRequest<T extends Entity>(entity: T): (arg?) => T {
  // noinspection TypeScriptValidateTypes because it gives a false negative
  return () => {
    entity.pendingRequest = true;
    return entity;
  };
}

export function clearPendingRequest<T extends Entity>(entity: T): (arg?) => T {
  // noinspection TypeScriptValidateTypes because it gives a false negative
  return () => {
    entity.pendingRequest = false;
    return entity;
  };
}
