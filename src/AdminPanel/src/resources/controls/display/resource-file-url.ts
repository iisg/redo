import {Resource} from "../../resource";
import {autoinject} from "aurelia-dependency-injection";
import {BasenameValueConverter} from "common/value-converters/basename";

@autoinject
export class ResourceFileUrlValueConverter implements ToViewValueConverter {
  constructor(private basenameValueConverter: BasenameValueConverter) {
  }

  toView(filename: string, resource: Resource): any {
    const fileBasename = this.basenameValueConverter.toView(filename);
    return `/api/resources/${resource.id}/files/${fileBasename}`;
  }
}
