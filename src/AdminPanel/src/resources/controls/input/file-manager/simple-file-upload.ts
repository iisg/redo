import {autoinject} from "aurelia-dependency-injection";
import {HttpClient, HttpResponseMessage} from "aurelia-http-client";
import {bindable} from "aurelia-templating";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {random, times} from 'lodash';
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataValue} from "resources/metadata-value";
import {Resource} from "resources/resource";
import {Alert} from "../../../../common/dialog/alert";
import {I18N} from "aurelia-i18n";
import {computedFrom} from "aurelia-binding";

@autoinject
export class SimpleFileUpload {
  @bindable inputValue: String;
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;
  selectedFiles: FileList;
  uploading: boolean = false;

  constructor(private httpClient: HttpClient, private alert: Alert, private i18n: I18N) {
  }

  uploadFiles() {
    if (this.selectedFiles && this.selectedFiles.length) {
      let promises = [];
      this.uploading = true;
      for (let index = 0; index < this.selectedFiles.length; index++) {
        const file = this.selectedFiles[index];
        const extensionAllowed = !this.metadata.constraints.allowedFileExtensions
          || this.metadata.constraints.allowedFileExtensions.includes(file.name.split('.').pop());
        if (this.skipValidation || extensionAllowed) {
          promises.push(this.uploadFile(file));
        } else {
          this.alert.show({type: "error"}, this.i18n.tr("Forbidden file extension"));
        }
      }
      Promise.all(promises)
        .catch(() => {
          this.alert.show(
            {type: "error"},
            this.i18n.tr("Could not upload the file"),
            this.i18n.tr("Ensure that the file is not too big and that it has the required format.")
          );
        })
        .finally(() => {
          this.inputValue = undefined;
          this.uploading = false;
        });
    }
  }

  @computedFrom('metadata.constraints.allowedFileExtensions')
  get allowedExtensions() {
    return this.metadata.constraints.allowedFileExtensions
      ? this.metadata.constraints.allowedFileExtensions.map(e => '.' + e).join(', ')
      : '';
  }

  private uploadFile(file: File): Promise<void> {
    const formData = new FormData();
    const reqId = times(20, () => random(35).toString(36)).join('');
    formData.append('reqid', reqId);
    formData.append('upload[]', file);
    formData.append('cmd', 'upload');
    formData.append('target', `l${this.metadata.constraints.simpleFileUploadTargetDir || 'resourceFiles'}_Lw`);
    formData.append('overwrite', '0');
    return this.httpClient.createRequest(SimpleFileUpload.fileUploadEndpoint(this.resource.id))
      .asPost()
      .withContent(formData)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then((message: HttpResponseMessage) => {
        const response = JSON.parse(message.response);
        if (response.added && response.added.length > 0) {
          const url = response.added[0].url.substr('file/'.length);
          this.resource.contents[this.metadata.id].push(new MetadataValue(url));
        } else {
          throw new Error('Upload file error');
        }
      });
  }

  private static fileUploadEndpoint(resourceId: number) {
    return `/resources/${resourceId}/file-manager`;
  }
}
