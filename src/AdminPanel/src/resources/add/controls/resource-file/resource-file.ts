import {bindable} from "aurelia-templating";
import {bindingMode} from "aurelia-binding";
import {Resource} from "../../../resource";
import {Metadata} from "../../../../resources-config/metadata/metadata";

export class ResourceFile {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  resource: Resource;
  @bindable({defaultBindingMode: bindingMode.twoWay})
  metadata: Metadata;

  originalFileName: string;

  constructor(private element: Element) {
  }

  bind() {
    this.originalFileName = this.resource.contents[this.metadata.baseId];
  }
}

export class FileNameValueConverter {
  toView(value) {
    if (value instanceof File) {
      return value.name;
    }
    let length = value.split('/').length;
    return value.split('/')[length - 1];
  }
}
