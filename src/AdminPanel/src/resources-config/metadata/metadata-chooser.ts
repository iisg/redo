import {autoinject} from "aurelia-dependency-injection";
import {I18N} from "aurelia-i18n";
import {bindable, customElement} from "aurelia-templating";
import {EntityChooser} from "common/components/entity-chooser/entity-chooser";
import {MetadataRepository} from "./metadata-repository";
import {Metadata} from "./metadata";
import {InCurrentLanguageValueConverter} from "../multilingual-field/in-current-language";
import {SystemMetadata} from "./system-metadata";

@customElement('metadata-chooser')
@autoinject
export class MetadataChooser extends EntityChooser {
  @bindable resourceClass: string | string[];
  @bindable control: string | string[];
  @bindable requiredKindId: number;
  @bindable additionalMetadataIds: number[] = [];
  @bindable excludedIds: number | number[] = [];
  private loadingMetadataList = false;
  private reloadMetadataList = false;

  constructor(private metadataRepository: MetadataRepository,
              private inCurrentLanguage: InCurrentLanguageValueConverter,
              i18n: I18N,
              element: Element) {
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
      const controlMetadata = this.metadataRepository.getListQuery()
        .filterByResourceClasses(this.resourceClass)
        .filterByControls(this.control)
        .filterByRequiredKindIds(this.requiredKindId)
        .excludeIds(this.excludedIds)
        .onlyTopLevel()
        .get();
      const additionalMetadata = this.additionalMetadataIds.length > 0
        ? this.metadataRepository.getListQuery()
          .filterByIds(this.additionalMetadataIds)
          .get()
        : [];
      Promise.all([additionalMetadata, controlMetadata])
        .then(metadataListList => this.sortMetadata(([] as Metadata[]).concat(...metadataListList)))
        .then(metadataList => {
          this.loadingMetadataList = false;
          if (this.reloadMetadataList) {
            this.reloadMetadataList = false;
            this.loadMetadataList();
          } else {
            this.entities = metadataList;
          }
        })
        .finally(() => this.loadingMetadataList = false);
    }
  }

  private sortMetadata(metadata: Metadata[]): Metadata[] {
    const resourceLabel = metadata.find(m => m.id === SystemMetadata.RESOURCE_LABEL.id);
    const sorted = metadata
      .filter(m => m.id != SystemMetadata.RESOURCE_LABEL.id)
      .sort((m1, m2) => {
        const m1Label = this.inCurrentLanguage.toView(m1.label).toLowerCase();
        const m2Label = this.inCurrentLanguage.toView(m2.label).toLowerCase();
        if (m1Label > m2Label) {
          return 1;
        }
        if (m1Label < m2Label) {
          return -1;
        }
        return 0;
      });
    if (resourceLabel) {
      sorted.unshift(resourceLabel);
    }
    return sorted;
  }

  get isFetchingOptions() {
    return this.loadingMetadataList;
  }
}
