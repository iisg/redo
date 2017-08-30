import {CustomValidationRule} from "../custom-validation-rules";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";
import {ConstraintValidator} from "./constraints/constraint-validator";
import {MaxCountConstraintValidator} from "./constraints/max-count";
import {autoinject, Container} from "aurelia-dependency-injection";

@autoinject
export class MetadataConstraintsSatisfiedValidationRule implements CustomValidationRule {
  static readonly NAME: string = 'MetadataConstraintsSatisfied';

  private readonly constraintValidatorClasses: any[] = [
    MaxCountConstraintValidator,
  ];

  private readonly constraintValidators: ConstraintValidator[];

  private constraintValidatorMap: StringMap<ConstraintValidator> = {};

  constructor(private container: Container) {
    this.constraintValidators = this.constraintValidatorClasses.map(validatorClass => container.get(validatorClass));
    for (const constraintValidator of this.constraintValidators) {
      this.constraintValidatorMap[constraintValidator.validatedConstraintName()] = constraintValidator;
    }
  }

  name(): string {
    return MetadataConstraintsSatisfiedValidationRule.NAME;
  }

  validationFunction(): (contents, metadata) => Promise<boolean> {
    return (contents: NumberMap<any[]>, resource: Resource): Promise<boolean> => {
      const validationResults: Array<boolean|Promise<boolean>> = [];
      const baseMetadataMap = this.getBaseMetadataMap(resource);
      for (const baseMetadataId in contents) {
        const values = contents[baseMetadataId];
        const metadata = baseMetadataMap[baseMetadataId];
        for (const constraintName in metadata.constraints) {
          const constraintArgument = metadata.constraints[constraintName];
          const validator = this.constraintValidatorMap[constraintName];
          if (validator !== undefined) {
            validationResults.push(validator.validate(values, constraintArgument));
          }
        }
      }
      return Promise.all(validationResults).then(
        (validationResults: boolean[]) => validationResults.reduce((v1, v2) => v1 && v2, true)
      );
    };
  }

  private getBaseMetadataMap(resource: Resource): NumberMap<Metadata> {
    const map = {};
    for (const metadata of resource.kind.metadataList) {
      map[metadata.baseId] = metadata;
    }
    return map;
  }
}
