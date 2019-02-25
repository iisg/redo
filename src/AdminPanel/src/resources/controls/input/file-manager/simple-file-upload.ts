import {bindable} from "aurelia-templating";
import {autoinject} from "aurelia-dependency-injection";
import {HttpClient, HttpResponseMessage} from "aurelia-http-client";
import {Resource} from "resources/resource";
import {Metadata} from "resources-config/metadata/metadata";
import {suppressError as suppressErrorHeader} from "common/http-client/headers";
import {times, random} from 'lodash';
import {MetadataValue} from "resources/metadata-value";

@autoinject
export class SimpleFileUpload {
  @bindable resource: Resource;
  @bindable metadata: Metadata;
  @bindable skipValidation: boolean;

  selectedFiles: FileList;
  uploading: boolean = false;

  constructor(private httpClient: HttpClient) {
  }

  onFileSelected() {
    if (this.selectedFiles && this.selectedFiles.length) {
      this.sendFile(this.selectedFiles[0]);
    }
  }

  private sendFile(file: File) {
    this.uploading = true;
    const formData = new FormData();
    const reqId = times(20, () => random(35).toString(36)).join('');
    formData.append('reqid', reqId);
    formData.append('upload[]', file);
    formData.append('cmd', 'upload');
    formData.append('target', 'lresourceFiles_Lw');
    formData.append('overwrite', '0');
    this.httpClient.createRequest(SimpleFileUpload.fileUploadEndpoint(this.resource.id))
      .asPost()
      .withContent(formData)
      .withHeader(suppressErrorHeader.name, suppressErrorHeader.value)
      .send()
      .then((message: HttpResponseMessage) => {
        const response = JSON.parse(message.response);
        const url = response.added[0].url.substr('file/'.length);
        this.resource.contents[this.metadata.id].push(new MetadataValue(url));
      })
      .finally(() => this.uploading = false);
  }

  private static fileUploadEndpoint(resourceId: number) {
    return `/resources/${resourceId}/file-manager`;
  }
}
