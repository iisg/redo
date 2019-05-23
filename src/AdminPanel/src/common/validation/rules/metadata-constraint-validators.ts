import {MetadataArrayConstraintValidator, SingleValueConstraintValidator} from "./constraints/constraint-validator";
import {MaxCountConstraintValidator} from "./constraints/max-count-constraint-validator";
import {autoinject, Container} from "aurelia-dependency-injection";
import {MinCountConstraintValidator} from "./constraints/min-count-constraint-validator";
import {MinMaxValueConstraintValidator} from "./constraints/min-max-value-constraint-validator";
import {AllowedFileExtensionsConstraintValidator} from "./constraints/allowed-file-extensions-constraint-validator";

@autoinject
export class MetadataConstraintValidators {
  private readonly singleValueValidatorClasses: any[] = [
    MinMaxValueConstraintValidator,
    AllowedFileExtensionsConstraintValidator,
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
