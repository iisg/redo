import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {computedFrom} from "aurelia-binding";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";
import {FloatingAddFormController} from "../add-form/floating-add-form";

@autoinject
export class MetadataList implements ComponentAttached {
  newMetadata: Metadata = new Metadata;
  isValidating: boolean = false;
  metadataList: Metadata[];
  addFormController: FloatingAddFormController = {};

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
      this.addFormController.hide();
      this.newMetadata = new Metadata;
      this.metadataList.push(response.content);
    });
  }
}
