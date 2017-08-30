import {ConstraintValidator} from "./constraint-validator";

describe(ConstraintValidator.name, () => {
  it('automatically generates constraint names', () => {
    class TestDummyConstraintValidator extends ConstraintValidator {
      validate(values: any[], config): boolean {
        fail();
        return false;
      }
    }
    const dummy = new TestDummyConstraintValidator();
    expect(dummy.validatedConstraintName()).toEqual('testDummy');
  });
});
