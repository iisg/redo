import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {Resource} from "resources/resource";
import {Metadata} from "resources-config/metadata/metadata";
import {random, times} from 'lodash';
import {computedFrom} from "aurelia-binding";
import {FileUploaderType} from "resources-config/metadata/constraint/file-uploader-type/file-uploader-type-editor";

@autoinject
export class FileUpload {
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;
  @bindable disabled: boolean;
  @bindable forceSimpleFileUpload: boolean = false;

  @computedFrom('metadata.constraints')
  get useSimpleUpload(): boolean {
    return this.forceSimpleFileUpload
      || !this.metadata.constraints.fileUploaderType
      || this.metadata.constraints.fileUploaderType === FileUploaderType.SIMPLE;
  }
}
