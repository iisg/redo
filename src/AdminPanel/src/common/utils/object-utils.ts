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

// https://stackoverflow.com/a/22266891
export function shallowEquals(a: any, b: any): boolean {
  for (let key in a) {
    if (a.hasOwnProperty(key)) {
      if (!(key in b) || a[key] !== b[key]) {
        return false;
      }
    }
  }
  for (let key in b) {
    if (b.hasOwnProperty(key)) {
      if (!(key in a) || a[key] !== b[key]) {
        return false;
      }
    }
  }
  return true;
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
