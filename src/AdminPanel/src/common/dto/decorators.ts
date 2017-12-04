import {FactoryFunction, registerMapper, TypeRegistry} from "./registry";
import {Container} from "aurelia-dependency-injection";
import {AutoMapper} from "./auto-mapper";
import {CopyMapper} from "./mappers";
import {addDtoProperty, inferHandlingInstruction} from "./class-metadata-utils";
import {propertyKeys} from "../utils/object-utils";
import {EntityClass, MapperClass} from "./contracts";

// A SHORT TUTORIAL FOR DECORATORS
//
// Decorators are just functions.
// If a function is used without parentheses, ie. @foo, it's a regular decorator.
// When used with parentheses, ie. @foo(), it's a decorator factory. It receives arguments and should return a plain decorator.
// If a functions is intended to be used both with and without parentheses, it has to guess which case it is by inspecting its arguments.
// That's why some decorator functions have complex ifs, return themselves if first argument is undefined etc.
//
// Decorator targets (ie. the first argument of decorator function) are tricky. Class decorators receive class itself. Property decorators
// receive prototype of instance objects. Some (in-)equalities to help you wrap your head about this and show the difference:
//   @deco class Foo {  // target === Foo
//     @deco bar;       // target === Foo.prototype === Object.getPrototypeOf(new Foo())
//   }
//   Foo !== Foo.prototype !== Object.getPrototypeOf(Foo);
//   (new Foo()).constructor === Foo === Foo.prototype.constructor

/* *****************************
 *   ENTITY CLASS DECORATORS   *
 *******************************/

/**
 * Registers a mapper for that class.
 * Keep in mind that registration happens when a class is imported. If it's not, but another class has fields of this type, mapping them
 * won't use mapper registered with @mappedWith() and most likely will fail. To avoid it, make sure entities are preloaded or declare
 * converters explicitly on properties.
 * Optionally, a factory function can be registered. These are used to create new entities for hydration when no instance is available,
 * for example when deserializing arrays of annotated object type.
 */
export function mappedWith<T>(mapper: MapperClass<T>, factory?: FactoryFunction<T>): (entityClass: EntityClass<T>) => void {
  return (entityClass: EntityClass<T>) => {
    if (Container.instance === undefined) {  // not available in tests
      return;
    }
    if (factory === undefined) {
      factory = () => new entityClass();
    }
    const registry: TypeRegistry = Container.instance.get(TypeRegistry);
    registry.register(entityClass.NAME, mapper, factory);
  };
}

/**
 * Shorthand for @mappedWith(CopyMapper).
 */
export function copyable(entityClass: EntityClass<any>): void {
  mappedWith(CopyMapper, () => {
    return new entityClass();
  })(entityClass);
}

/**
 * Shorthand for @mappedWith(AutoMapper, ...).
 * FactoryFunction may be omitted if class has an argument-less constructor.
 * Example:
 * @automapped Foo {...}
 * @automapped(() => new Bar('baz')) Bar {...}
 */
export function automapped(factory?: FactoryFunction<any> | EntityClass<any>): any {
  if ('prototype' in factory) {
    // Used as a decorator factory, factory is actually EntityClass, not a factory.
    const entityClass = factory as any as EntityClass<any>;
    mappedWith(AutoMapper)(entityClass);
  } else {
    // Used as a decorator, factory is really a factory (or undefined, which is also fine).
    return mappedWith(AutoMapper, factory as FactoryFunction<any>);
  }
}

/**
 * Equivalent of decorating class with @automapped and all its properties with @map.
 * This decorator doesn't support factory functions and must be applied without parentheses.
 */
export function allPropertiesAutomapped(entityClass: EntityClass<any>): void {
  const instance = new entityClass();
  for (const key of propertyKeys(instance)) {
    map(entityClass.prototype, key);
  }
  automapped(entityClass);
}

/* *************************
 *   PROPERTY DECORATORS   *
 ***************************/

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
 *   @map('Foo') foo: Foo;                      // custom class type (avoid Foo.NAME as it may cause declaration order issues)
 *   @map('String[]') childrenNames: string[];  // arrays require explicit typing
 *   @map(CopyMapper) children: Object[];       // use specified mapper, ignore type
 * }
 */
export function map<T>(typeOrMapper: MapperClass<T> | string | T, propertyName?: string): any {
  if (propertyName === undefined) {
    // Used as a decorator factory, typeOrMapper is mapper's class or name of property's class or class itself.
    if (typeOrMapper === undefined) {
      throw new Error("Received undefined type/mapper argument. Possibly a mapper for field is defined further in the bundle. "
        + "Tag problematic mapper with @maps('someName') and use @map('someName') on problematic property.");
    }
    return (entityClass: EntityClass<T>, propertyName: string) => {
      addDtoProperty(entityClass, propertyName, typeOrMapper as MapperClass<any> | string);
    };
  } else {
    // Used as a decorator, typeOrMapper is entity prototype. Appropriate mapping method has to be guessed from TS type metadata.
    const prototype = (typeOrMapper as T);
    const handlingInstruction = inferHandlingInstruction(prototype, propertyName);
    addDtoProperty(prototype, propertyName, handlingInstruction);
  }
}

/**
 * Shorthand for @map(CopyMapper). Indicates that a property can be deep-copied. It will strip all methods!
 */
export function copy(entityClass: any, propertyName: string): void {
  map(CopyMapper)(entityClass, propertyName);
}

/* ********************************
 *   CONVERTER CLASS DECORATORS   *
 **********************************/

/**
 * Inverse of @mappedWith - used on Mapper implementation to declare supported types.
 * Handy when you want to map arrays, hashmaps etc. in unusual way - it will override default behavior.
 * Ensure that decorated mappers are loaded before they are used, otherwise type registry won't know about them.
 */
export function maps<T>(...typesOrTypeNames: Array<string | EntityClass<T>>): (mapperClass: MapperClass<T>) => void {
  return (mapperClass: MapperClass<T>) => {
    registerMapper(mapperClass, typesOrTypeNames);
  };
}
