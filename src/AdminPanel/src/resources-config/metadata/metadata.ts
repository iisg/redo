import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {ResourceKind} from "../resource-kind/resource-kind";
import {MetadataRepository} from "./metadata-repository";
import {deepCopy} from "common/utils/object-utils";
import {Entity} from "common/entity/entity";
import {SystemResourceKinds} from "../resource-kind/system-resource-kinds";
import {arraysEqual} from "common/utils/array-utils";
import {computedFrom} from "aurelia-binding";
import {automapped, map, mappedWith} from "common/dto/decorators";
import {ResourceKindConstraintMapper, MetadataMapper} from "./metadata-mapping";

export interface MultilingualText extends StringStringMap {
}

@automapped(() => new MetadataConstraints())
export class MetadataConstraints {
  @map maxCount?: number;
  @map(ResourceKindConstraintMapper) resourceKind?: ResourceKind[]|number[] = [];

  constructor(initialValues?: MetadataConstraints) {
    $.extend(this, initialValues);
  }
}

export const metadataConstraintDefaults: MetadataConstraints = {
  resourceKind: [],
  maxCount: 0,
};

@mappedWith(MetadataMapper, () => new Metadata())
export class Metadata extends Entity {
  @map id: number;
  @map name: string = '';
  @map label: MultilingualText = {};
  @map placeholder: MultilingualText = {};
  @map description: MultilingualText = {};
  @map control: string = 'text';
  @map parentId: number;
  @map baseId: number;
  @map(MetadataConstraints.name) constraints: MetadataConstraints = new MetadataConstraints();
  @map shownInBrief: boolean;
  @map resourceClass: string;

  @computedFrom('control', 'constraints.resourceKind')
  get canDetermineAssignees(): boolean {
    return this.control == 'relationship'
      && arraysEqual(this.constraints.resourceKind, [SystemResourceKinds.USER_ID]);
  }

  clearInheritedValues(metadataRepository: MetadataRepository, baseId?: number): Promise<Metadata> {
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

  static copyContents(source: Object, target: Metadata): Metadata {
    let cloned: Metadata = deepCopy(source);
    cloned.constraints = $.extend(new MetadataConstraints(), cloned.constraints);
    return $.extend(target, cloned);
  }
}

export function registerMetadataValidationRules() {
  ValidationRules
    .ensure('label').displayName('Label').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('control').displayName('Control').required()
    .ensure('name').displayName('Name').required()
    .on(Metadata);
}
