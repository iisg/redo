import {MetadataArrayConstraintValidator, SingleValueConstraintValidator} from "./constraints/constraint-validator";
import {MaxCountConstraintValidator} from "./constraints/max-count-constraint-validator";
import {RegexConstraintValidator} from "./constraints/regex-constraint-validator";
import {autoinject, Container} from "aurelia-dependency-injection";
import {MinCountConstraintValidator} from "./constraints/min-count-constraint-validator";
import {MinMaxValueConstraintValidator} from "./constraints/min-max-value-constraint-validator";
import {NoOpenTreeConstraintValidator} from './constraints/no-open-tree-constraint-validator';

@autoinject
export class MetadataConstraintValidators {
  private readonly singleValueValidatorClasses: any[] = [
    RegexConstraintValidator,
    MinMaxValueConstraintValidator,
    NoOpenTreeConstraintValidator,
  ];

  private readonly metadataArrayValidatorClasses: any[] = [
    MinCountConstraintValidator,
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
