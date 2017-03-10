import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {ResourceKind} from "../resource-kind/resource-kind";
import {ResourceKindRepository} from "../resource-kind/resource-kind-repository";

export interface MultilingualTextType extends StringStringMap {
}

export class Metadata {
  id: number;
  name: String = '';
  label: MultilingualTextType = {};
  placeholder: MultilingualTextType = {};
  description: MultilingualTextType = {};
  control: String = 'text';
  parentId: number;
  baseId: number;
  constraints: MetadataConstraints = new MetadataConstraints();
}

export class MetadataConstraints {
  resourceKind: ResourceKind[]|number[] = [];

  fetchResourceKindEntities(repository: ResourceKindRepository) {
    repository.getList().then(resourceKinds => {
      let idToResourceKind = new Map<number, ResourceKind>();
      for (let resourceKind of resourceKinds) {
        idToResourceKind[resourceKind.id] = resourceKind;
      }
      const entities: ResourceKind[] = [];
      for (let id of this.resourceKind as number[]) {
        entities.push(idToResourceKind[id]);
      }
      this.resourceKind = entities;
    });
  }
}

export function registerMetadataValidationRules() {
  ValidationRules
    .ensure('label').displayName('Label').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('control').displayName('Control').required()
    .ensure('name').displayName('Name').required()
    .on(Metadata);
}
