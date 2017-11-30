import {Resource} from "../../../resources/resource";
import {ResourceKind} from "../resource-kind";
import * as moment from "moment";

export class SampleResource extends Resource {
  constructor(resourceKind: ResourceKind) {
    super();
    this.id = 13;
    this.kind = resourceKind;
    this.resourceClass = this.kind.resourceClass;
    this.createSampleContents();
  }

  private createSampleContents() {
    for (let metadata of this.kind.metadataList) {
      this.contents[metadata.baseId] = this.createSampleContent(metadata.control);
    }
  }

  private createSampleContent(control: string): any[] {
    switch (control) {
      case 'integer':
        return [1 + Math.floor(Math.random() * 100)];
      case 'boolean':
        return [Math.random() > .5];
      case 'date':
        return [moment().toDate()];
      case 'relationship':
        return [1234];
      case 'file':
        return ['i6/r6/repeka.pdf'];
      default:
        return ['XXX', 'YYY'];
    }
  }
}
