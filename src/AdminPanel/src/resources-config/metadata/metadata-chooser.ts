import {Metadata} from "./metadata";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "./metadata-repository";
import {twoWay} from "common/components/binding-mode";

@autoinject
export class MetadataChooser {
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;
  @bindable excludedMetadata: Metadata[];
  @bindable resourceClass: string | string[];
  @bindable control: string | string[];

  metadataList: Metadata[];

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    this.loadMetadataList();
  }

  resourceClassChanged() {
    if (this.metadataList) {
      this.loadMetadataList();
    }
  }

  controlChanged() {
    if (this.metadataList) {
      this.loadMetadataList();
    }
  }

  private async loadMetadataList() {
    this.metadataList = await this.metadataRepository.getListQuery()
      .filterByResourceClasses(this.resourceClass)
      .filterByControls(this.control)
      .onlyTopLevel()
      .get();
  }
}
