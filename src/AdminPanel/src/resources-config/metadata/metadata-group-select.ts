import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {observable} from "aurelia-binding";
import {twoWay} from "../../common/components/binding-mode";
import {MetadataGroup} from "./metadata";
import {MetadataGroupRepository} from "./metadata-group-repository";

@autoinject
export class MetadataGroupSelect implements ComponentAttached {
  @bindable(twoWay) groupId: string;
  @bindable disabled: boolean;
  groups: MetadataGroup[];
  @observable value: MetadataGroup;

  private initialized = false;

  constructor(private metadataGroupRepository: MetadataGroupRepository) {
  }

  attached() {
    this.loadGroupList()
      .then(() => this.updateDropdown())
      .then(() => this.initialized = true);
  }

  private loadGroupList() {
    return Promise.resolve(this.metadataGroupRepository.getList())
      .then(groups => {
        if (groups.filter(group => group.id === MetadataGroup.defaultGroupId).length === 0) {
          return [...groups, {id: MetadataGroup.defaultGroupId, label: {}}];
        }
        return groups;
      })
      .then(groups => this.groups = groups);
  }

  valueChanged() {
    if (!this.initialized) {
      return;
    }
    this.updateSelectedGroupId();
  }

  groupIdChanged() {
    if (!this.initialized) {
      return;
    }
    this.updateDropdown();
  }

  private updateSelectedGroupId() {
    if (!this.value) {
      this.groupId = MetadataGroup.defaultGroupId;
      return;
    }
    const groups = this.groups.filter(group => group.id === this.value.id);
    this.groupId = groups[0] && groups[0].id;
  }

  private updateDropdown() {
    this.value = this.groups.filter(group => group.id === this.groupId)[0];
  }
}
