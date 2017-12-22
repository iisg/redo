import {MetadataArrayConstraintValidator, SingleValueConstraintValidator} from "./constraints/constraint-validator";
import {MaxCountConstraintValidator} from "./constraints/max-count-constraint-validator";
import {RegexConstraintValidator} from "./constraints/regex-constraint-validator";
import {autoinject, Container} from "aurelia-dependency-injection";

@autoinject
export class MetadataConstraintValidators {
  private readonly singleValueValidatorClasses: any[] = [
    RegexConstraintValidator,
  ];

  private readonly metadataArrayValidatorClasses: any[] = [
    MaxCountConstraintValidator,
  ];

  public readonly singleValueValidators: SingleValueConstraintValidator[];
  public readonly metadataArrayValidators: MetadataArrayConstraintValidator[];

  constructor(private container: Container) {
    this.singleValueValidators = this.validatorClassesToInstances(this.singleValueValidatorClasses);
    this.metadataArrayValidators = this.validatorClassesToInstances(this.metadataArrayValidatorClasses);
  }

  private validatorClassesToInstances(classes: any[]) {
    const instances = classes.map(validatorClass => this.container.get(validatorClass));
    instances.forEach((instance) => instances[instance.validatedConstraintName()] = instance);
    return instances;
  }
}
