import {autoinject} from "aurelia-dependency-injection";
import {HttpClient, HttpResponseMessage} from "aurelia-http-client";
import {bindable} from "aurelia-templating";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {random, times} from 'lodash';
import {Metadata} from "resources-config/metadata/metadata";
import {MetadataValue} from "resources/metadata-value";
import {Resource} from "resources/resource";

@autoinject
export class SimpleFileUpload {
  @bindable inputValue: String;
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;
  selectedFiles: FileList;
  uploading: boolean = false;

  constructor(private httpClient: HttpClient) {
  }

  uploadFiles() {
    if (this.selectedFiles && this.selectedFiles.length) {
      let promises = [];
      this.uploading = true;
      for (let index = 0; index < this.selectedFiles.length; index++) {
        promises.push(this.uploadFile(this.selectedFiles[index]));
      }
      Promise.all(promises).finally(() => {
        this.inputValue = undefined;
        this.uploading = false;
      });
    }
  }

  private uploadFile(file: File): Promise<void> {
    const formData = new FormData();
    const reqId = times(20, () => random(35).toString(36)).join('');
    formData.append('reqid', reqId);
    formData.append('upload[]', file);
    formData.append('cmd', 'upload');
    formData.append('target', 'lresourceFiles_Lw');
    formData.append('overwrite', '0');
    return this.httpClient.createRequest(SimpleFileUpload.fileUploadEndpoint(this.resource.id))
      .asPost()
      .withContent(formData)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then((message: HttpResponseMessage) => {
        const response = JSON.parse(message.response);
        const url = response.added[0].url.substr('file/'.length);
        this.resource.contents[this.metadata.id].push(new MetadataValue(url));
      });
  }

  private static fileUploadEndpoint(resourceId: number) {
    return `/resources/${resourceId}/file-manager`;
  }
}
