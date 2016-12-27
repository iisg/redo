import {Metadata} from "resources-config/metadata/metadata";
import {Resource} from "resources/resource";

export class ControlInput {
  metadata: Metadata;

  resource: Resource;

  activate(model: {metadata: Metadata, resource: Resource}) {
    this.metadata = model.metadata;
    this.resource = model.resource;
  }
}
