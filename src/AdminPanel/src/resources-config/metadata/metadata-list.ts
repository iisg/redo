import {HttpClient} from "aurelia-http-client";
import {autoinject} from "aurelia-dependency-injection";
import {ComponentAttached} from "aurelia-templating";
import {Metadata} from "./metadata";
import {FloatingAddFormController} from "../add-form/floating-add-form";

@autoinject
export class MetadataList implements ComponentAttached {
  metadataList: Metadata[];
  addFormController: FloatingAddFormController = {};

  constructor(private httpClient: HttpClient) {
  }

  attached(): void {
    this.httpClient.get('metadata').then(response => {
      this.metadataList = response.content;
    });
  }

  addNewMetadata(newMetadata: Metadata) {
    return this.httpClient.post('metadata', newMetadata).then(response => {
      this.addFormController.hide();
      this.metadataList.push(response.content);
    });
  }
}
