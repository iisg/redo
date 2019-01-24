import {autoinject} from "aurelia-dependency-injection";
import {MetadataGroup} from "./metadata";
import {MetadataGroupRepository} from "./metadata-group-repository";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";
import {I18N} from "aurelia-i18n";

@autoinject
export class MetadataGroupLabelValueConverter implements ToViewValueConverter {
  private groups: AnyMap<MetadataGroup>;
  private configHasDefaultGroup = false;

  constructor(private metadataGroupRepository: MetadataGroupRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              private i18n: I18N) {
  }

  toView(groupId: string): string {
    if (!this.configHasDefaultGroup && groupId === MetadataGroup.defaultGroupId) {
      return this.i18n.tr("Other metadata");
    }
    this.fetchGroups();
    const group = this.groups && this.groups[groupId];
    if (group) {
      return this.inCurrentLanguage.toView(group.label);
    } else {
      return `${this.i18n.tr("Unknown group")} (${groupId})`;
    }
  }

  private fetchGroups() {
    if (!this.groups) {
      this.groups = this.metadataGroupRepository.getList()
        .reduce(
          (obj, group) => ({...obj, [group.id]: group}), {}
        );
    }
    if (this.groups.hasOwnProperty(MetadataGroup.defaultGroupId)) {
      this.configHasDefaultGroup = true;
    }
  }
}
