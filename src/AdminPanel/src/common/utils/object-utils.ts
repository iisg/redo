// http://ilikekillnerds.com/2015/12/deep-cloning-objects-in-javascript-without-dependencies/
export function deepCopy(object: any): any {
  return JSON.parse(JSON.stringify(object));
}

export function isObject(object: any): boolean {
  return typeof object == 'object';
}

export function keysByValue<M, V>(obj: M, value: V): string[] {
  return Object.keys(obj).filter(key => obj[key] === value);
}

export function numberKeysByValue<M, V>(obj: M, value: V): number[] {
  return keysByValue(obj, value).map(key => parseInt(key, 10)).filter(val => !Number.isNaN(val));
}

export function propertyKeys(obj: Object): string[] {
  return Object.keys(obj)
    .filter(key => typeof obj[key] != 'function')  // remove methods
    .filter(key => !key.startsWith('_'));  // remove internal properties like Aurelia's __observe__
}

export function zip<V>(keys: Array<number|string>, values: V[]): AnyMap<V> {
  if (keys.length != values.length) {
    throw new Error(`Key and value arrays must be of the same length, actually are ${keys.length} and ${values.length} items long.`);
  }
  const obj = {};
  keys.forEach((key, i) => obj[key] = values[i]);
  return obj;
}

export function values<T>(map: AnyMap<T>): T[] {
  return Object.keys(map).map(k => map[k]);
}
