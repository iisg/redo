/**
 * Checks if two arrays have the same elements in identical order.
 * http://stackoverflow.com/a/19746771/1937994
 */
export function arraysEqual(array1: any[], array2: any[]) {
  return array1.length == array2.length && array1.every((val1, index) => val1 == array2[index]);
}

/**
 * Removes duplicates from array.
 * https://stackoverflow.com/a/14438954/1937994
 */
export function unique(values: any[]): any[] {
  return values.filter((value, index, self) => self.indexOf(value) === index);
}

export function removeByValue<T>(array: T[], value: T): void {
  const index = array.indexOf(value);
  if (index != -1) {
    array.splice(index, 1);
  }
}
