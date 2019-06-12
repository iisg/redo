/**
 * Checks if two arrays have the same elements in identical order.
 * http://stackoverflow.com/a/19746771/1937994
 */
export function arraysEqual(array1: any[], array2: any[]) {
  return array1 != undefined && array2 != undefined
    && array1.length == array2.length
    && array1.every((val1, index) => val1 == array2[index]);
}

/**
 * Removes duplicates from array.
 * https://stackoverflow.com/a/14438954/1937994
 */
export function unique<T>(values: T[], compare: (a: T, b: T) => boolean = (a, b) => a == b): T[] {
  return values.filter((value, index, self) => self.findIndex(a => compare(value, a)) === index);
}

export function containsDuplicates(values: any[]): boolean {
  return values.length != unique(values).length;
}

export function removeValue<T>(array: T[], value: T): void {
  const index = array.indexOf(value);
  if (index != -1) {
    array.splice(index, 1);
  }
}

// http://stackoverflow.com/a/10865042/878514
export function flatten<T>(arrayOfArrays: Array<T | T[]>): T[] {
  return [].concat.apply([], arrayOfArrays);
}

export function inArray<T>(needle: T, haystack: T[]): boolean {
  return haystack && haystack.indexOf(needle) >= 0;
}

export function move<T>(array: T[], element: T, delta: number): void {
  const currentIndex = array.indexOf(element);
  if (currentIndex >= 0) {
    let desiredIndex = currentIndex + delta;
    desiredIndex = Math.min(Math.max(0, desiredIndex), array.length - 1);
    array.splice(desiredIndex, 0, array.splice(currentIndex, 1)[0]);
  }
}

export function diff<T>(array1: T[], array2: any[]): T[] {
  return array1.filter(item => !inArray(item, array2));
}

/**
 * Converts array of objects to one object
 * https://stackoverflow.com/a/45906909
 */
export function convertToObject<T>(array: T[]): Object {
  return Object.assign({}, ...array);
}

export function isEmptyArray<T>(value): boolean {
  return Array.isArray(value) && (value as Array<T>).length === 0;
}
