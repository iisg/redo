import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {computedFrom, bindingMode} from "aurelia-binding";

@autoinject
export class MetadataChildForm {
  submitting: boolean = false;
  metadataList: Metadata[];
  baseMetadata: Metadata;
  @bindable submit: (value: {baseMetadata: Metadata}) => Promise<Metadata>;
  @bindable metadataChildrenList: Metadata[];
  @bindable({defaultBindingMode: bindingMode.twoWay})
  hasMetadataToChoose: boolean;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached(): void {
    this.metadataList = undefined;
    this.metadataRepository.getList().then((metadataList) => {
      this.metadataList = metadataList;
    });
  }

  submitChild() {
    this.submitting = true;
    return this.submit({baseMetadata: this.baseMetadata})
      .then(() => this.baseMetadata = new Metadata()).finally(() => this.submitting = false);
  }

  @computedFrom("metadataList.length", "metadataChildrenList.length")
  get notUsedMetadata() {
    let usedIds = this.metadataChildrenList ? this.metadataChildrenList.map(metadata => metadata.baseId) : [];
    let notUsedMetadata = this.metadataList ? this.metadataList.filter(metadata => usedIds.indexOf(metadata.id) < 0) : [];
    this.hasMetadataToChoose = !this.metadataList || notUsedMetadata.length > 0;
    return notUsedMetadata;
  }
}
