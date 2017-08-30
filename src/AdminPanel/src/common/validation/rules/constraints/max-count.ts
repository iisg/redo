import {ConstraintValidator} from "./constraint-validator";

export class MaxCountConstraintValidator extends ConstraintValidator {
  validatedConstraintName(): string {
    return 'maxCount';
  }

  validate(values: any[], maxCount: number): boolean {
    return maxCount == 0 || values.length <= maxCount;
  }
}
