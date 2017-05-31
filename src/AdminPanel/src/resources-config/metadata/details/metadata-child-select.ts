import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {bindingMode} from "aurelia-binding";

@autoinject
export class MetadataChildSelect {
  @bindable submit: (value: {baseMetadata: Metadata}) => Promise<Metadata>;
  @bindable metadataChildrenList: Metadata[];
  @bindable({defaultBindingMode: bindingMode.twoWay}) hasMetadataToChoose: boolean;

  submitting: boolean = false;
  metadataList: Metadata[];
  baseMetadata: Metadata;

  constructor(private metadataRepository: MetadataRepository) {
  }

  async attached() {
    this.metadataList = await this.metadataRepository.getList();
  }

  submitChild() {
    this.submitting = true;
    return this.submit({baseMetadata: this.baseMetadata})
      .then(() => this.baseMetadata = undefined)
      .finally(() => this.submitting = false);
  }
}
