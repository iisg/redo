import {autoinject, Container} from "aurelia-dependency-injection";
import {Mapper, CopyMapper, ArrayMapper, IdentityMapper, TypedMapMapper} from "./mappers";

@autoinject
export class TypeRegistry {
  private mappers: StringMap<Function> = {};
  private factories: StringMap<FactoryFunction<any>> = {};

  constructor(private container: Container) {
    this.registerDefaults();
  }

  private registerDefaults(): void {
    const undefinedFactory = () => undefined;
    this.register(Number.name, IdentityMapper, undefinedFactory);
    this.register('number', IdentityMapper, undefinedFactory);
    this.register(String.name, IdentityMapper, undefinedFactory);
    this.register('string', IdentityMapper, undefinedFactory);
    this.register(Boolean.name, IdentityMapper, undefinedFactory);
    this.register('boolean', IdentityMapper, undefinedFactory);
    this.register(Object.name, CopyMapper, () => {
      return {};
    });
  }

  register(type: string, mapperClass: Function, factory?: FactoryFunction<any>): void {
    if (type in this.mappers) {
      throw new Error(`'${type}' already has mapper registered: '${this.mappers[type].name}'`);
    }
    this.mappers[type] = mapperClass;
    if (factory !== undefined) {
      this.factories[type] = factory;
    }
  }

  getMapperByType(type: string): Mapper<any> {
    const mapperClass = this.mappers[type];
    const arrayItemType = this.extractArrayItemType(type);
    const mapValueType = this.extractMapValueType(type);
    if (mapperClass !== undefined) {
      return this.instantiateMapper(mapperClass);
    } else if (arrayItemType !== undefined) {
      return this.getArrayMapperByItemType(arrayItemType);
    } else if (mapValueType !== undefined) {
      return this.getTypedMapMapperByValueType(mapValueType);
    }
    return undefined;
  }

  private extractArrayItemType(arrayType: string): string {
    const match = arrayType.match(/\[]$/);
    return match ? arrayType.substr(0, match.index) : undefined;
  }

  private extractMapValueType(mapType: string): string {
    const match = mapType.match(/^\{(.+)}$/);
    return match ? match[1] : undefined;
  }

  private getArrayMapperByItemType<I>(itemType: string): Mapper<I[]> {
    const itemMapper = this.getMapperByType(itemType);
    return itemMapper && new ArrayMapper<I>(itemMapper as any, this.getFactoryByType(itemType));
  }

  private getTypedMapMapperByValueType<V>(valueType: string): Mapper<AnyMap<V>> {
    const valueMapper = this.getMapperByType(valueType);
    return valueMapper && new TypedMapMapper<V>(valueMapper as any, this.getFactoryByType(valueType));
  }

  private instantiateMapper(mapperClass: Function) {
    return this.container.get(mapperClass);
  }

  getMapper(typeOrClass: string|Function) {
    return (typeOrClass['apply'])
      ? this.instantiateMapper(typeOrClass as Function)
      : this.getMapperByType(typeOrClass as string);
  }

  getEntityByType(type: string): any {
    const factory = this.factories[type];
    if (factory === undefined) {
      throw new Error(`No factory registered for type '${type}'`);
    }
    return factory();
  }

  getFactoryByType(type: string): FactoryFunction<any> {
    const registeredFactory = this.factories[type];
    if (registeredFactory !== undefined) {
      return registeredFactory;
    } else if (this.extractArrayItemType(type)) {
      return () => [];
    } else if (this.extractMapValueType(type)) {
      return () => {
      };
    }
    return undefined;
  }
}

export type FactoryFunction<T> = () => T;

export function registerMapper(mapperClass: Function, typesOrTypeNames: Array<string|Function>): void {
  if (Container.instance === undefined) {  // not available in tests
    return;
  }
  if (!Array.isArray(typesOrTypeNames)) {
    typesOrTypeNames = [typesOrTypeNames] as any;
  }
  const registry: TypeRegistry = Container.instance.get(TypeRegistry);
  for (const typeOrName of typesOrTypeNames) {
    const typeName = (typeof typeOrName == 'string') ? typeOrName as string : (typeOrName as Function).name;
    registry.register(typeName, mapperClass);
  }
}
