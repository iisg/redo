import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {booleanAttribute} from "../boolean-attribute";
import {twoWay} from "../binding-mode";
import {Resource} from "../../../resources/resource";

@autoinject
export class FileUpload implements ComponentAttached {
  @bindable(twoWay) value: File | string;
  @bindable resource: Resource;
  @bindable @booleanAttribute disabled: boolean = false;

  files: Array<File>;

  fileNameInput: Element;

  fileSelected(files: File[]): void {
    this.value = files[0];
  }

  valueChanged() {
    $(this.fileNameInput).val(this.displayName);
  }

  attached(): void {
    // valueChanged() might have fired before fileNameInput is assigned, make sure it fires after attaching
    this.valueChanged();
  }

  @computedFrom('value')
  get isFile() {
    if (!this.value) {
      return false;
    }
    const file = this.value as File; // it may not be a File, need to test it!
    return file.type;
  }

  @computedFrom('value')
  get isImage() {
    const fileType = this.isFile;
    return fileType && fileType.split('/')[0] == 'image';
  }

  @computedFrom('value')
  get displayName(): string {
    if (!this.value) {
      return '';
    }
    const file = this.value as File;
    return file.name ? file.name : this.value as string;
  }
}
