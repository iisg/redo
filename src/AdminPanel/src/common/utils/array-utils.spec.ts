import {flatten, arraysEqual} from "./array-utils";

describe('array-utils', () => {
  describe(arraysEqual.name, () => {
    it('compares arrays', () => {
      const a1 = ['qwe', 'asd'];
      const a2 = a1.concat();
      const a3 = a1.concat('zxc');
      expect(arraysEqual(a1, a2)).toBe(true);
      expect(arraysEqual(a1, a3)).toBe(false);
    });

    it('compares strictly', () => {
      const a1 = [false];
      const a2 = [undefined];
      expect(arraysEqual(a1, a2)).toBe(false);
    });

    it('tolerates undefined', () => {
      const a = [];
      expect(arraysEqual(undefined, a)).toBe(false);
      expect(arraysEqual(a, undefined)).toBe(false);
    });
  });

  describe(flatten.name, () => {
    it("flattens", () => {
      const nested = ['one', ['two', 'three'], 'four'];
      const flattened = flatten(nested);
      expect(flattened.length).toBe(4);
      expect(flattened).toEqual(['one', 'two', 'three', 'four']);
    });
  });
});
