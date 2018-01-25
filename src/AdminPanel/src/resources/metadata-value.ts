export class MetadataValue {
  value: any;
  submetadata: NumberMap<MetadataValue[]> = {};

  constructor(value: any = undefined) {
    this.value = value;
  }
}
