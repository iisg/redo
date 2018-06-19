import {map, toPairs} from "lodash";

/**
 * Maps an object to an array containing all of its own values.
 * {a: 1, b: 2} -> [{key: "a", value: 1}, {key: "b", value: 2}]
 */
export class ObjectToArrayValueConverter implements ToViewValueConverter {
  toView(value: any): any[] {
    return map(toPairs(value), ([key, value]) => {
      return {key, value};
    });
  }
}
