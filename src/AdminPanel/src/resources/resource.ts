import {ResourceKind} from "resources-config/resource-kind/resource-kind";
import {ValidationRules} from "aurelia-validation";

export class Resource {
  id: number;
  kind: ResourceKind;
  contents: StringAnyMap = {};
}

// ugly hack to disable the rules in the unit testing, see: https://github.com/aurelia/validation/issues/377#issuecomment-267791805
if ((ValidationRules as any).parser) {
  ValidationRules
    .ensure('kind').displayName("Resource kind").required()
    .ensure('contents').satisfies(contents => Object.keys(contents).filter(metadataId => !!contents[metadataId]).length > 0)
    .withMessageKey('atLeastOneMetadataRequired')
    .on(Resource);
}
