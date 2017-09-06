import {flatten} from "./array-utils";

describe('array-utils', () => {
  describe(flatten.name, () => {
    it("flattens", () => {
      const nested = ['one', ['two', 'three'], 'four'];
      const flattened = flatten(nested);
      expect(flattened.length).toBe(4);
      expect(flattened).toEqual(['one', 'two', 'three', 'four']);
    });
  });
});
