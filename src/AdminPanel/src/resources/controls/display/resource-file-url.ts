import {Resource} from "resources/resource";
import {autoinject} from "aurelia-dependency-injection";

@autoinject
export class ResourceFileUrlValueConverter implements ToViewValueConverter {
  toView(filename: string, resource: Resource): any {
    return `/api/resources/${resource.id}/file/${filename}`;
  }
}
