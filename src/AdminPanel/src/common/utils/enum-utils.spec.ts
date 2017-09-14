import {successor} from "./enum-utils";

describe('enum-utils', () => {
  describe(successor.name, () => {
    enum TestEnum {
      ONE, TWO, THREE
    }

    it('returns successor', () => {
      expect(successor(TestEnum.ONE, TestEnum)).toBe(TestEnum.TWO);
      expect(successor(TestEnum.TWO, TestEnum)).toBe(TestEnum.THREE);
    });

    it('wraps after last value', () => {
      expect(successor(TestEnum.THREE, TestEnum)).toBe(TestEnum.ONE);
    });
  });
});
