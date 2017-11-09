import {Resource} from "./resource";

describe('resource', () => {
  describe('filterUndefinedValues', () => {
    it('returns resource without undefined values', () => {
      const resource = new Resource();
      resource.contents = {1: [""], 2: [{}, undefined], 3: [undefined]};
      resource.filterUndefinedValues();
      expect(resource.contents).toEqual({1: [""], 2: [{}], 3: []});
    });
  });
});
