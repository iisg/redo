import {flatten, arraysEqual, move, diff} from "./array-utils";

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

  describe(move.name, () => {
    it("moves the element backward", () => {
      const array = ['a', 'b', 'c'];
      move(array, 'b', -1);
      expect(array).toEqual(['b', 'a', 'c']);
    });

    it("moves the element forward", () => {
      const array = ['a', 'b', 'c'];
      move(array, 'b', 1);
      expect(array).toEqual(['a', 'c', 'b']);
    });

    it("moves the element to the beginning if can't more", () => {
      const array = ['a', 'b', 'c'];
      move(array, 'b', -100);
      expect(array).toEqual(['b', 'a', 'c']);
    });

    it("moves the element to the end if can't more", () => {
      const array = ['a', 'b', 'c'];
      move(array, 'b', 100);
      expect(array).toEqual(['a', 'c', 'b']);
    });

    it("does not move non existing elements", () => {
      const array = ['a', 'b', 'c'];
      move(array, 'x', 1);
      expect(array).toEqual(['a', 'b', 'c']);
    });
  });

  describe(diff.name, () => {
    it('subtracts', () => {
      const arr1 = ['foo', 'bar', 'baz'];
      const arr2 = ['bar', 'quux'];
      expect(diff(arr1, arr2)).toEqual(['foo', 'baz']);
    });
  });
});
