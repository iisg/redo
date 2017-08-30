import {CustomValidationRule} from "../custom-validation-rules";
import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";
import {ConstraintValidator} from "./constraints/constraint-validator";
import {MaxCountConstraintValidator} from "./constraints/max-count";

export class MetadataConstraintsSatisfiedValidationRule implements CustomValidationRule {
  static readonly NAME: string = MetadataConstraintsSatisfiedValidationRule.name;

  private readonly constraintValidators: ConstraintValidator[] = [
    new MaxCountConstraintValidator(),
  ];

  private constraintValidatorMap: StringMap<ConstraintValidator> = {};

  constructor() {
    for (const constraintValidator of this.constraintValidators) {
      this.constraintValidatorMap[constraintValidator.validatedConstraintName()] = constraintValidator;
    }
  }

  name(): string {
    return MetadataConstraintsSatisfiedValidationRule.name;
  }

  validationFunction(): (contents, metadata) => boolean {
    return (contents: NumberMap<any[]>, resource: Resource) => {
      const baseMetadataMap = this.getBaseMetadataMap(resource);
      for (const baseMetadataId in contents) {
        const values = contents[baseMetadataId];
        const metadata = baseMetadataMap[baseMetadataId];
        for (const constraintName in metadata.constraints) {
          const constraintArgument = metadata.constraints[constraintName];
          const validator = this.constraintValidatorMap[constraintName];
          if (validator !== undefined && !validator.validate(values, constraintArgument)) {
            return false;
          }
        }
      }
      return true;
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
