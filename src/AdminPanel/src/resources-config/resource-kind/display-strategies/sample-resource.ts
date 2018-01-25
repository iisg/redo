import {Resource} from "../../../resources/resource";
import {ResourceKind} from "../resource-kind";
import * as moment from "moment";
import {MetadataValue} from "../../../resources/metadata-value";

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
      this.contents[metadata.id] = this.createSampleContent(metadata.control);
    }
  }

  private createSampleContent(control: string): MetadataValue[] {
    switch (control) {
      case 'integer':
        return [new MetadataValue(1 + Math.floor(Math.random() * 100))];
      case 'boolean':
        return [new MetadataValue(Math.random() > .5)];
      case 'date':
        return [new MetadataValue(moment().toDate())];
      case 'relationship':
        return [new MetadataValue(1234)];
      case 'file':
        return [new MetadataValue('i6/r6/repeka.pdf')];
      default:
        return [new MetadataValue('XXX'), new MetadataValue('YYY')];
    }
  }
}
