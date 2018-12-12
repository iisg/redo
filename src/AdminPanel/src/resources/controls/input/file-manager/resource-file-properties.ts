import {bindable} from "aurelia-templating";
import {DeduplicatingHttpClient} from "common/http-client/deduplicating-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {Resource} from "../../../resource";

@autoinject
export class ResourceFileProperties {
  @bindable path: string;
  @bindable resource: Resource;
  private fileInfo;
  private fetching = false;

  constructor(private httpClient: DeduplicatingHttpClient) {
  }

  fetchFileInfo() {
    if (!this.fetching) {
      this.fetching = true;
      this.httpClient.get(`resources/${this.resource.id}/file-manager?cmd=size&targets[]=${this.fileHash}`)
        .then(response => {
          if (response.content.dirCnt) {
            this.fileInfo = {files: response.content.fileCnt, directories: response.content.dirCnt - 1, size: response.content.size};
          } else {
            this.fileInfo = {size: response.content.size};
          }
        })
        .finally(() => this.fetching = false);
    }
  }

  // https://github.com/Studio-42/elFinder/wiki/Getting-encoded-hash-from-the-path
  @computedFrom('path')
  get fileHash(): string {
    if (this.path) {
      const firstHashIndex = this.path.indexOf('/');
      const volumeId = this.path.substr(0, firstHashIndex);
      const path = this.path.substr(firstHashIndex + 1);
      const hash = btoa(unescape(encodeURIComponent(path)))
        .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '.')
        .replace(/\.+$/, '');
      return `l${volumeId}_${hash}`;
    } else {
      return '';
    }
  }
}
