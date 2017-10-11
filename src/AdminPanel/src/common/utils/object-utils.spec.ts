import {keysByValue, numberKeysByValue} from "./object-utils";
describe('object-utils', () => {
  describe(keysByValue.name, () => {
    const testObject = {
      a: 1,
      b: 1,
      c: true,
      d: false,
      e: 2,
      f: true,
      g: 'test',
      h: undefined,
      i: 0,
      j: 'test',
      k: 'test2',
      10: 'number',
    };

    it('counts ints properly', () => {
      expect(keysByValue(testObject, 0)).toEqual(['i']);
      expect(keysByValue(testObject, 1)).toEqual(['a', 'b']);
      expect(keysByValue(testObject, 2)).toEqual(['e']);
    });

    it('counts bools properly', () => {
      expect(keysByValue(testObject, true)).toEqual(['c', 'f']);
      expect(keysByValue(testObject, false)).toEqual(['d']);
    });

    it('counts strings properly', () => {
      expect(keysByValue(testObject, 'test')).toEqual(['g', 'j']);
      expect(keysByValue(testObject, 'test2')).toEqual(['k']);
    });

    it('counts undefined properly', () => {
      expect(keysByValue(testObject, undefined)).toEqual(['h']);
    });

    it('returns numbers as strings', () => {
      expect(keysByValue(testObject, 'number')).toEqual(['10']);
    });
  });

  describe(numberKeysByValue.name, () => {
    const testObject = {
      a: 'xyz',
      1: 'xyz',
      10: 'number',
    };

    it('returns numbers as numbers', () => {
      expect(numberKeysByValue(testObject, 'number')).toEqual([10]);
    });

    it('filters out non-numbers', () => {
      expect(numberKeysByValue(testObject, 'xyz')).toEqual([1]);
    });
  });
});
