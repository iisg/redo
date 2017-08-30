import {ConstraintValidator} from "./constraint-validator";

describe(ConstraintValidator.name, () => {
  it('automatically generates constraint names', () => {
    class TestDummyConstraintValidator extends ConstraintValidator {
      validate(): Promise<boolean> {
        fail();
        return Promise.resolve(false);
      }
    }
    const dummy = new TestDummyConstraintValidator();
    expect(dummy.validatedConstraintName()).toEqual('testDummy');
  });
});
