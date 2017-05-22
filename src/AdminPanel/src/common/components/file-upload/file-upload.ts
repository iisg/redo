import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode, computedFrom} from "aurelia-binding";

@autoinject
export class FileUpload implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: File;
  files: Array<File>;

  fileNameInput: Element;

  fileSelected(files: File[]): void {
    this.value = files[0];
  }

  valueChanged(file: File) {
    $(this.fileNameInput).val(file ? file.name : '');
  }

  attached(): void {
    // valueChanged() might have fired before fileNameInput is assigned, make sure it fires after attaching
    this.valueChanged(this.value);
  }

  @computedFrom('value')
  get isImage() {
    return this.value && this.value.type.split('/')[0] == 'image';
  }
}
