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
