import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient} from "aurelia-http-client";
import {bindingMode, computedFrom} from "aurelia-binding";

@autoinject
export class FileUpload {
  @bindable({defaultBindingMode: bindingMode.twoWay})
  value: File;
  files: Array<File>;

  constructor(private element: Element, private httpClient: HttpClient) {
  }

  addFiles(files): void {
    this.value = files[0];
  }

  @computedFrom('value')
  get isImage() {
    if (this.files) {
      return this.value.type.split('/')[0] == 'image';
    }
  }
}
