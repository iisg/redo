import {propertyNamesToCamelCase, underscoreToCamelCase} from "./repository-utils";

describe("repository-utils", () => {
  describe(underscoreToCamelCase.name, () => {
    it("converts to camel case", () => {
      expect(underscoreToCamelCase("the_goal")).toEqual("theGoal");
    });

    it("handles multiple underscores", () => {
      expect(underscoreToCamelCase("the___goal")).toEqual("theGoal");
    });

    it("skips underscores and the beginning and at the end", () => {
      expect(underscoreToCamelCase("_the_goal_")).toEqual("theGoal");
    });
  });

  describe(propertyNamesToCamelCase.name, () => {
    it("leaves good properties intact", () => {
      let converted: any = propertyNamesToCamelCase({theGoal: 1});
      expect(converted.theGoal).toEqual(1);
    });

    it("converts underscore property to camel case", () => {
      let converted: any = propertyNamesToCamelCase({the_goal: 1});
      expect(converted.theGoal).toEqual(1);
      expect(converted.the_goal).toBeUndefined();
    });

    it("does not change the original object", () => {
      let original = {the_goal: 1};
      propertyNamesToCamelCase(original);
      expect(original.the_goal).toEqual(1);
      expect((original as any).theGoal).toBeUndefined();
    });
  });
});
