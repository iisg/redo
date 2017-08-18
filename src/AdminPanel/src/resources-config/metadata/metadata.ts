import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {ResourceKind} from "../resource-kind/resource-kind";
import {ResourceKindRepository} from "../resource-kind/resource-kind-repository";
import {MetadataRepository} from "./metadata-repository";
import {deepCopy} from "common/utils/object-utils";

export interface MultilingualText extends StringStringMap {
}

export class Metadata {
  id: number;
  name: string = '';
  label: MultilingualText = {};
  placeholder: MultilingualText = {};
  description: MultilingualText = {};
  control: string = 'text';
  parentId: number;
  baseId: number;
  constraints: MetadataConstraints = new MetadataConstraints();
  shownInBrief: boolean;

  public clearInheritedValues(metadataRepository: MetadataRepository, baseId?: number): Promise<Metadata> {
    const baseMetadata: Promise<Metadata> = (baseId != undefined)
      ? metadataRepository.get(baseId)
      : metadataRepository.getBase(this);
    return baseMetadata.then(base => {
      for (let overridableField of ['label', 'placeholder', 'description']) {
        for (let languageCode in this[overridableField]) {
          if (this[overridableField][languageCode] == base[overridableField][languageCode]) {
            this[overridableField][languageCode] = '';
          }
        }
      }
      return this;
    });
  }

  public static clone(metadata: Object): Metadata {
    let cloned = deepCopy(metadata);
    cloned.constraints = $.extend(new MetadataConstraints(), metadata['constraints']);
    return $.extend(new Metadata(), cloned);
  }

  public static createFromBase(baseMetadata: Metadata): Metadata {
    let metadata = Metadata.clone(baseMetadata);
    metadata.baseId = baseMetadata.id;
    return metadata;
  }
}

export class MetadataConstraints {
  resourceKind: ResourceKind[]|number[] = [];

  fetchResourceKindEntities(repository: ResourceKindRepository): Promise<ResourceKind[]> {
    return repository.getList().then(resourceKinds => {
      let idToResourceKind = new Map<number, ResourceKind>();
      for (let resourceKind of resourceKinds) {
        idToResourceKind[resourceKind.id] = resourceKind;
      }
      const entities: ResourceKind[] = [];
      for (let id of this.resourceKind as number[]) {
        entities.push(idToResourceKind[id]);
      }
      return this.resourceKind = entities;
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
