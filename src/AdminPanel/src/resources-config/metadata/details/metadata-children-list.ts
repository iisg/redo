import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "../metadata";
import {MetadataRepository} from "../metadata-repository";
import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";

@autoinject
export class MetadataChildrenList implements ComponentAttached {
  @bindable metadata: Metadata;
  @bindable(({defaultBindingMode: bindingMode.twoWay}))
  metadataChildrenList: Metadata[];
  addFormOpened: boolean = false;

  constructor(private metadataRepository: MetadataRepository) {
  }

  attached(): void {
    this.metadataChildrenList = undefined;
    this.metadataRepository.getChildren(this.metadata.id).then(metadataChildrenList => this.metadataChildrenList = metadataChildrenList);
  }
}
