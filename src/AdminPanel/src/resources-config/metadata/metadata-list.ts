import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";

@autoinject
export class MetadataList implements ComponentAttached {
  newMetadata: Metadata = new Metadata;
  newMetadataFormVisible: boolean = false;
  isValidating: boolean = false;
  metadataList: Metadata[];

  constructor(private httpClient: HttpClient) {
  }

  @computedFrom('isValidating', 'httpClient.isRequesting')
  get isRequesting() {
    return this.isValidating || this.httpClient.isRequesting;
  }

  attached(): void {
    this.httpClient.get('metadata').then(response => {
      this.metadataList = response.content;
    });
  }

  addNewMetadata() {
    return this.httpClient.post('metadata', this.newMetadata).then(response => {
      this.newMetadataFormVisible = false;
      this.newMetadata = new Metadata;
      this.metadataList.push(response.content);
    });
  }
}
