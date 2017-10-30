import {TypeRegistry, FactoryFunction, registerMapper} from "./registry";
import {Container} from "aurelia-dependency-injection";
import {AutoMapper} from "./auto-mapper";
import {CopyMapper} from "./mappers";
import {addDtoProperty, inferHandlingInstruction} from "./class-metadata-utils";
import {propertyKeys} from "../utils/object-utils";

// A SHORT TUTORIAL FOR DECORATORS
//
// Decorators are just functions.
// If a function is used without parentheses, ie. @foo, it's a regular decorator.
// When used with parentheses, ie. @foo(), it's a decorator factory. It receives arguments and should return a plain decorator.
// If a functions is intended to be used both with and without parentheses, it has to guess which case it is by inspecting its arguments.
// That's why some decorator functions have complex ifs, return themselves if first argument is undefined etc.

/*******************************/
/*   ENTITY CLASS DECORATORS   */
/*******************************/

/**
 * Registers a mapper for that class.
 * Keep in mind that registration happens when a class is imported. If it's not, but another class has fields of this type, mapping them
 * won't use mapper registered with @mappedWith() and most likely will fail. To avoid it, make sure entities are preloaded or declare
 * converters explicitly on properties.
 * Optionally, a factory function can be registered. These are used to create new entities for hydration when no instance is available,
 * for example when mapping deserializing arrays of annotated object type.
 */
export function mappedWith(mapper: Function, factory?: FactoryFunction<any>): (entityClass: Object) => void {
  return (entityClass: Object) => {
    if (Container.instance === undefined) {  // not available in tests
      return;
    }
    const registry: TypeRegistry = Container.instance.get(TypeRegistry);
    const className = (entityClass as Function).name || entityClass.constructor.name;
    registry.register(className, mapper, factory);
  };
}

/**
 * Shorthand for @mappedWith(CopyMapper).
 */
export function copyable(entityClass: Object): void {
  mappedWith(CopyMapper, () => {
    return {};
  })(entityClass);
}

/**
 * Shorthand for @mappedWith(AnnotatedMapper, ...).
 * FactoryFunction may be left undefined, but deserialization and array mapping won't work for that class. Hydration will still work.
 * Example:
 * @automapped(() => new Foo()) Foo {...}
 */
export function automapped(factory: FactoryFunction<any>): (entityClass: Object) => void {
  return mappedWith(AutoMapper, factory);
}

export function allPropertiesAutomapped(factoryFunction: FactoryFunction<Object>): (entityClass: Object) => void {
  const instance = factoryFunction();
  return (entityClass: Object) => {
    for (const key of propertyKeys(instance)) {
      map(entityClass, key);
    }
    automapped(factoryFunction)(entityClass);
  };
}

/***************************/
/*   PROPERTY DECORATORS   */
/***************************/

/**
 * Marks a property as having corresponding DTO fields. Property may map to multiple DTO fields or fields named differently, it's up to
 * the mapper to decide which properties in DTO map to this property in entity.
 * The argument can be either type name or a mapper class. This decorator can also be used without arguments - in that case, it will
 * attempt to retrieve type from TypeScript-emitted metadata. This may fail in some cases and an exception will be thrown:
 * - When type is in a circular dependency (eg. when type A has fields of type B and type B has fields of type A). In this case TS won't
 *   generate type metadata and guessing is not possible.
 * - When property has an array type, ie. Array<Foo> or Foo[]. Type metadata don't contain member types, so appropriate mapper can't be
 *   chosen automatically.
 *
 * Example:
 * class Foo {
 *   @map id: number;
 *   @map name: string;
 *   @map('Number') refCount: number;           // optional explicit type
 *   @map(Number.name) childrenCount: number;   // this is okay too
 *   @map('String[]') childrenNames: string[];  // arrays require explicit typing
 *   @map(CopyMapper) children: Object[];       // use specified mapper, ignore type
 * }
 */
export function map(typeOrMapper: any, propertyName?: string): any {
  if (propertyName === undefined) {
    // Used as a decorator factory, typeOrMapper is mapper's class or name of property's type.
    return (entityClass: Object, propertyName: string) => {
      addDtoProperty(entityClass, propertyName, typeOrMapper);
    };
  } else {
    // used as a decorator, typeOrMapper is entity type. Appropriate mapping method has to be guessed from TS type metadata.
    const handlingInstruction = inferHandlingInstruction(typeOrMapper as Function, propertyName);
    addDtoProperty(typeOrMapper, propertyName, handlingInstruction);
  }
}

/**
 * Shorthand for @map(CopyMapper). Indicates that a property can be deep-copied. It will strip all methods!
 */
export function copy(entityClass: Object, propertyName: string): void {
  map(CopyMapper)(entityClass, propertyName);
}

/**********************************/
/*   CONVERTER CLASS DECORATORS   */
/**********************************/

/**
 * Inverse of @mappedWith - used on Mapper implementation to declare supported types.
 * Useful for converting interfaces because they can't be decorated.
 */
export function maps(...typesOrTypeNames: Array<string|Function>): (entityClass: Object) => void {
  return (entityClass: Object) => {
    registerMapper(entityClass as any, typesOrTypeNames);
  };
}
