/**
 * Checks if two arrays have the same elements in identical order.
 * http://stackoverflow.com/a/19746771/1937994
 */
export function arraysEqual(array1: any[], array2: any[]) {
  return array1.length == array2.length && array1.every((val1, index) => val1 == array2[index]);
}
