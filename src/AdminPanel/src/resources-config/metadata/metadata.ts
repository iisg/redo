import {ValidationRules} from "aurelia-validation";
import {RequiredInAllLanguagesValidationRule} from "common/validation/rules/required-in-all-languages";
import {ResourceKind} from "../resource-kind/resource-kind";
import {MetadataRepository} from "./metadata-repository";
import {Entity} from "common/entity/entity";
import {automapped, map, mappedWith} from "common/dto/decorators";
import {MetadataMapper, MinMaxConstraintMapper, ResourceKindConstraintMapper} from "./metadata-mapping";
import {MinMaxValue} from "./metadata-min-max-value";
import {MetadataControl} from "./metadata-control";
import {FrontendConfig} from "../../config/FrontendConfig";
import {AutoMapperWithCustomProperties} from "../../common/dto/auto-mapper";
import {FileUploaderType} from "./constraint/file-uploader-type/file-uploader-type-editor";
import {DateMode} from "../../resources/controls/input/flexible-date-input/flexible-date-config";

export interface MultilingualText extends StringStringMap {
}

export interface SupportedMetadataConstraintDefinition {
  name: string;
  hasConfiguration: boolean;
}

@mappedWith(AutoMapperWithCustomProperties)
export class MetadataConstraints {
  static NAME = 'MetadataConstraints';

  minCount?: number;
  @map maxCount?: number;
  @map(ResourceKindConstraintMapper) resourceKind?: ResourceKind[] | number[] = [];
  @map regex?: string;
  @map relatedResourceMetadataFilter?: NumberMap<string> = {};
  @map(MinMaxConstraintMapper) minMaxValue?: MinMaxValue = new MinMaxValue();
  @map doublePrecision?: number;
  @map fileUploaderType?: string;
  @map simpleFileUploadTargetDir?: string;
  @map relationshipSelectorType?: string = FileUploaderType.SIMPLE;
  @map('string[]') allowedFileExtensions?: string[];
  @map flexibleDateMode?: DateMode = DateMode.FLEXIBLE;

  constructor(initialValues?: MetadataConstraints) {
    $.extend(this, initialValues);
  }

  public static getSupportedConstraints(metadata: Metadata): SupportedMetadataConstraintDefinition[] {
    return FrontendConfig.get('control_constraints')[metadata.control];
  }
}

@automapped
export class MetadataGroup {
  static NAME = 'MetadataGroup';
  static readonly defaultGroupId = 'DEFAULT_GROUP';

  @map id: string;
  @map label: MultilingualText;

  constructor(initialValues?: MetadataGroup) {
    $.extend(this, initialValues);
  }
}

function isRegexDeclarationValid(regex: string): boolean {
  try {
    new RegExp(regex);
  } catch (e) {
    return false;
  }
  return true;
}

export function registerMetadataConstraintsValidationRules() {
  ValidationRules
    .ensure('minMaxValue').satisfies(obj => obj === undefined || obj.min === undefined || obj.max === undefined
    || Number.isInteger(obj.min) && Number.isInteger(obj.max) && obj.max >= obj.min)
    .withMessageKey('minMaxValueRange')
    .ensure('maxCount').satisfies(obj => obj === undefined || Number.isInteger(obj) && (obj > 0 || obj === -1))
    .withMessageKey('minimalMaxCount')
    .ensure('regex').satisfies(obj => obj === undefined || isRegexDeclarationValid(obj.toString()))
    .withMessageKey('invalidRegex')
    .on(MetadataConstraints);
}

export const metadataConstraintDefaults: MetadataConstraints = {
  resourceKind: [],
  minCount: 0,
  maxCount: undefined,
  minMaxValue: {min: undefined, max: undefined},
  regex: '',
  relatedResourceMetadataFilter: {}
};

export interface GroupMetadataList {
  groupId: string;
  metadataList: Metadata[];
  childMetadata: boolean;
}

@mappedWith(MetadataMapper)
export class Metadata extends Entity {
  static NAME = 'Metadata';

  @map id: number;
  @map name: string = '';
  @map label: MultilingualText = {};
  @map placeholder: MultilingualText = {};
  @map description: MultilingualText = {};
  @map control: MetadataControl = MetadataControl.TEXT;
  @map parentId: number;
  @map baseId: number;
  @map constraints: MetadataConstraints = new MetadataConstraints();
  @map groupId: string;
  @map displayStrategy: string;
  @map shownInBrief: boolean;
  @map copyToChildResource: boolean;
  @map resourceClass: string;
  @map canDetermineAssignees: boolean;
  @map hasChildren: boolean;

  get isDynamic(): boolean {
    return this.displayStrategy !== undefined;
  }

  async clearInheritedValues(metadataRepository: MetadataRepository, originalMetadata: Metadata = undefined): Promise<Metadata> {
    if (!originalMetadata) {
      originalMetadata = await metadataRepository.get(this.id);
    }
    for (let overridableField of ['label', 'placeholder', 'description']) {
      for (let languageCode in this[overridableField]) {
        if (this[overridableField].hasOwnProperty(languageCode)) {
          if (this[overridableField][languageCode] == originalMetadata[overridableField][languageCode]) {
            this[overridableField][languageCode] = '';
          }
        }
      }
    }
    return this;
  }
}

export function registerMetadataValidationRules() {
  ValidationRules
    .ensure('label').displayName('Label').satisfiesRule(RequiredInAllLanguagesValidationRule.NAME)
    .ensure('control').displayName('Control').required()
    .ensure('name').displayName('Name').required()
    .ensure('displayStrategy').required().when((m: Metadata) => m.isDynamic)
    .on(Metadata);
}
