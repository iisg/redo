// http://ilikekillnerds.com/2015/12/deep-cloning-objects-in-javascript-without-dependencies/
export function deepCopy(object: any): any {
  return object ? JSON.parse(JSON.stringify(object)) : object;
}

export function isObject(object: any): boolean {
  return typeof object == 'object' && !Array.isArray(object);
}

export function isString(value: any): boolean {
  return typeof value == 'string';
}

export function keysByValue<M, V>(obj: M, value: V): string[] {
  return Object.keys(obj).filter(key => obj[key] === value);
}

export function numberKeysByValue<M, V>(obj: M, value: V): number[] {
  return keysByValue(obj, value).map(key => parseInt(key)).filter(val => !Number.isNaN(val));
}

export function propertyKeys(obj: Object): string[] {
  return Object.keys(obj)
    .filter(key => typeof obj[key] != 'function')  // remove methods
    .filter(key => !key.startsWith('_'));  // remove internal properties like Aurelia's __observe__
}

export function zip<V>(keys: Array<number | string>, values: V[]): AnyMap<V> {
  if (keys.length != values.length) {
    throw new Error(`Key and value arrays must be of the same length, actually are ${keys.length} and ${values.length} items long.`);
  }
  const obj = {};
  keys.forEach((key, i) => obj[key] = values[i]);
  return obj;
}

export function filterByValues<T>(obj: AnyMap<T>, predicate: (values: T) => boolean): AnyMap<T> {
  const result = {};
  for (const key in obj) {
    if (predicate(obj[key])) {
      result[key] = obj[key];
    }
  }
  return result;
}

export function mapValues<T, U>(obj: AnyMap<T>, mapperFn: (value: T) => U): AnyMap<U> {
  const out = {};
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      if (obj[key] instanceof Object) {
        out[key] = mapValues(obj[key] as any as AnyMap<T>, mapperFn);
      } else {
        out[key] = mapperFn(obj[key]);
      }
    }
  }
  return out;
}

export function mapValuesShallow<T, U>(obj: AnyMap<T>, mapperFn: (value: T) => U): AnyMap<U> {
  const out = {};
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
        out[key] = mapperFn(obj[key]);
    }
  }
  return out;
}

export function safeJsonParse(value: string): any {
  if (value) {
    try {
      return JSON.parse(value);
    }
    catch (exception) {
      console.warn(exception); // tslint:disable-line
    }
  }
  return undefined;
}

export function mapToArray<T, U>(obj: AnyMap<T>, mapper: (key: string | number, value: T) => U): U[] {
  return Object.keys(obj).map(key => mapper(key, obj[key]));
}
