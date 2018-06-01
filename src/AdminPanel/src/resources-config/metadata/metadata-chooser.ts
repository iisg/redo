import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable, customElement} from "aurelia-templating";
import {EntityChooser} from "common/components/entity-chooser/entity-chooser";
import {booleanAttribute} from "../../common/components/boolean-attribute";
import {Metadata} from "./metadata";
import {MetadataRepository} from "./metadata-repository";

@customElement('metadata-chooser')
@autoinject
export class MetadataChooser extends EntityChooser {
  @bindable resourceClass: string | string[];
  @bindable control: string | string[];
  @bindable @booleanAttribute setFirstAsDefault: boolean;
  private loadingMetadataList = false;
  private reloadMetadataList = false;

  constructor(private metadataRepository: MetadataRepository, i18n: I18N, element: Element) {
    super(i18n, element);
  }

  attached() {
    super.attached();
    this.loadMetadataList();
  }

  resourceClassChanged() {
    if (this.entities) {
      this.loadMetadataList();
    }
  }

  controlChanged() {
    if (this.entities) {
      this.loadMetadataList();
    }
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
            this.entities = metadataList;
            if (this.setFirstAsDefault) {
              if (this.value) {
                this.value = metadataList.find(metadata => metadata.id == (this.value as Metadata).id);
              } else {
                this.value = metadataList[0];
              }
            }
          }
        })
        .finally(() => this.loadingMetadataList = false);
    }
  }

  get isFetchingOptions() {
    return this.loadingMetadataList;
  }
}
