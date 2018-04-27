import {Metadata} from "./metadata";
import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {MetadataRepository} from "./metadata-repository";
import {twoWay} from "common/components/binding-mode";
import {booleanAttribute} from "../../common/components/boolean-attribute";

@autoinject
export class MetadataChooser {
  @bindable(twoWay) value: Metadata;
  @bindable(twoWay) hasMetadataToChoose: boolean;
  @bindable resourceClass: string | string[];
  @bindable control: string | string[];
  @bindable filter: (metadata: Metadata) => boolean = () => true;
  @bindable @booleanAttribute setFirstAsDefault: boolean;
  @bindable(twoWay) shouldRefreshResults: boolean;

  metadataList: Metadata[];

  private loadingMetadataList = false;
  private reloadMetadataList = false;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached() {
    this.loadMetadataList();
  }

  resourceClassChanged() {
    this.loadMetadataList();
  }

  controlChanged() {
    this.loadMetadataList();
  }

  private loadMetadataList() {
    if (this.loadingMetadataList) {
      this.reloadMetadataList = true;
    } else {
      this.loadingMetadataList = true;
      this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .filterByControls(this.control)
        .onlyTopLevel()
        .get()
        .then(metadataList => {
          this.loadingMetadataList = false;
          if (this.reloadMetadataList) {
            this.reloadMetadataList = false;
            this.loadMetadataList();
          } else {
            this.metadataList = metadataList;
          }
          if (this.setFirstAsDefault && !this.value) {
            this.value = metadataList[0];
          }
        })
        .finally(() => this.loadingMetadataList = false);
    }
  }
}
