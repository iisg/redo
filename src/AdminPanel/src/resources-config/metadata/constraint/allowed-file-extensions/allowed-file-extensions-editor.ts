import {bindable, ComponentAttached} from "aurelia-templating";
import {twoWay} from "../../../../common/components/binding-mode";
import {computedFrom, observable} from "aurelia-binding";

export class AllowedFileExtensionsEditor implements ComponentAttached {
  @bindable(twoWay) allowedExtensions: string[] = [];
  @bindable originalAllowedExtensions: string[];
  @bindable hasBase: boolean;

  @observable allowedExtensionsValue: string;

  attached(): void {
    this.allowedExtensionsValue = this.allowedExtensions.join(', ');
  }

  allowedExtensionsValueChanged() {
    this.allowedExtensions = this.allowedExtensionsValue
      ? this.allowedExtensionsValue.split(',').map(this.normalizeFileExtension)
      : undefined;
  }

  private normalizeFileExtension(extension: string) {
    extension = extension.trim().toLowerCase();
    return extension.startsWith('.') ? extension.substr(1) : extension;
  }

  resetToOriginalValues() {
    this.allowedExtensions = this.originalAllowedExtensions;
  }

  @computedFrom('allowedExtensions', 'originalAllowedExtensions')
  get wasModified(): boolean {
    return this.allowedExtensions != this.originalAllowedExtensions;
  }
}
