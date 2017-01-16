import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";

export class Resource {
  id: number;
  kind: ResourceKind;
  contents: StringAnyMap = {};
}

export function registerResourceValidationRules() {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => !!contents[metadataId]).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
