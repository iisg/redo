import {Metadata} from "resources-config/metadata/metadata";
import {inArray, ArrayPartition} from "common/utils/array-utils";
import {filterByValues} from "common/utils/object-utils";

export class XmlImportProcessor {
  processValueMap(valueMap: StringMap<string[]>, metadataList: Metadata[]): XmlImportProcessorResult {
    const result = new XmlImportProcessorResult();
    const metadataMap: StringMap<Metadata> = {};
    for (const metadata of metadataList) {
      metadataMap[metadata.baseId + ''] = metadata;
      metadataMap[metadata.name] = metadata;
    }

    for (const metadataId in valueMap) {
      if (!(metadataId in metadataMap)) {
        result.extraValues[metadataId] = valueMap[metadataId];
        continue;
      }
      const metadata = metadataMap[metadataId];
      const values = valueMap[metadataId];
      this.processMetadataValues(metadata, values, result);
    }

    result.trimEmptyKeys();
    if (Object.keys(result.rejectedValues).length == 0) {
      result.rejectedValues = undefined;
    }
    if (Object.keys(result.extraValues).length == 0) {
      result.extraValues = undefined;
    }
    return result;
  }

  private processMetadataValues(metadata: Metadata, values: string[], result: XmlImportProcessorResult): void {
    const metadataId = metadata.baseId + '';
    let normalized: any[];

    switch (metadata.control) {
      case 'text':
      case 'textarea':
        result.setAcceptedAndRejected(metadataId, values, []);
        break;
      case 'integer':
        normalized = values.map(str => str.match(/^\d+$/) ? parseInt(str, 10) : str);
        result.setByPartitioning(metadataId, normalized, value => typeof value == 'number');
        break;
      case 'boolean':
        const truthy = value => inArray(value, ['1', 'true']);
        const falsy = value => inArray(value, ['', '0', 'false']);
        normalized = values.map(value => truthy(value) ? true : falsy(value) ? false : value);
        result.setByPartitioning(metadataId, normalized, value => value === true || value === false);
        break;
      default:
        result.setAcceptedAndRejected(metadataId, [], values);
    }
  }
}

export class XmlImportProcessorResult {
  acceptedValues: StringMap<any[]> = {};
  rejectedValues: StringMap<string[]> = {};
  extraValues: StringMap<string[]> = {};

  setAcceptedAndRejected(metadataId: string, accepted: any[], rejected: string[]) {
    this.acceptedValues[metadataId] = accepted;
    this.rejectedValues[metadataId] = rejected;
  }

  setByPartitioning(metadataId: string, values: any[], predicate: (v: any) => boolean) {
    const partition = new ArrayPartition(values, predicate);
    this.acceptedValues[metadataId] = partition.truthy;
    this.rejectedValues[metadataId] = partition.falsy;
  }

  trimEmptyKeys(): void {
    this.acceptedValues = filterByValues(this.acceptedValues, values => values.length > 0);
    this.rejectedValues = filterByValues(this.rejectedValues, values => values.length > 0);
    this.extraValues = filterByValues(this.extraValues, values => values.length > 0);
  }
}
