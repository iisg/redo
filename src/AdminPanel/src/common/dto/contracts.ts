import {Mapper} from "./mappers";

/*
 * These interfaces are static class contracts. They match the class itself, not a class instance. Don't implement them!
 * No class instance will ever fulfill this contract, because instances don't have a constructor.
 */

export interface Class<T> {
  // noinspection JSUnusedLocalSymbols
  new (...args): T;
}

export interface EntityClass<T> extends Class<T> {
  NAME: string;
}

export interface MapperClass<T> extends Class<Mapper<T>> {
}
