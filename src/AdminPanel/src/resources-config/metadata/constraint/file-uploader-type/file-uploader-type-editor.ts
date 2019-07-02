import {bindable} from "aurelia-templating";
import {computedFrom} from "aurelia-binding";
import {twoWay} from "common/components/binding-mode";
import {values} from "lodash";

export class FileUploaderTypeEditor {
  @bindable(twoWay) value: string;
  @bindable originalValue: string;
  @bindable hasBase: boolean;
  values: string[] = values(FileUploaderType);

  @computedFrom('value', 'originalValue')
  get wasModified(): boolean {
    return this.value != this.originalValue;
  }

  resetToOriginalValues() {
    this.value = this.originalValue;
  }
}

export enum FileUploaderType {
  SIMPLE = 'simple',
  FILE_MANAGER = 'file_manager',
}
