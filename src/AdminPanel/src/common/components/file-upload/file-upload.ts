import {bindable, ComponentAttached} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {bindingMode, computedFrom} from "aurelia-binding";

@autoinject
export class FileUpload implements ComponentAttached {
  @bindable({defaultBindingMode: bindingMode.twoWay}) value: File|string;
  @bindable disabled: boolean = false;

  files: Array<File>;

  fileNameInput: Element;

  fileSelected(files: File[]): void {
    this.value = files[0];
  }

  valueChanged() {
    $(this.fileNameInput).val(this.displayName);
  }

  disabledChanged() {
    if (this.disabled as any === '') { // when used without value: <file-upload disabled>
      this.disabled = true;
    }
  }

  attached(): void {
    // valueChanged() might have fired before fileNameInput is assigned, make sure it fires after attaching
    this.valueChanged();
  }

  @computedFrom('value')
  get isImage() {
    if (!this.value) {
      return false;
    }
    const file = this.value as File; // it may not be a File, need to test it!
    return file.type && file.type.split('/')[0] == 'image';
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
