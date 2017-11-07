import {XmlImportProcessor} from "./xml-import-processor";
import {Metadata} from "resources-config/metadata/metadata";

function metadata(baseId: number, name: string, control: string): Metadata {
  return $.extend(new Metadata(), {baseId, name, control});
}

describe(XmlImportProcessor.name, () => {
  it('distributes accepted and rejected values', () => {
    const metadataList = [
      metadata(1, 'one', 'text'),
      metadata(2, 'two', 'integer'),
      metadata(3, 'three', 'textarea'),
      metadata(4, 'four', 'boolean'),
    ];
    const valueMap = {
      1: ['abc', '123', 'true'],
      2: ['abc', '123', 'true'],
      3: ['abc', '123', 'true'],
      4: ['abc', '123', 'true'],
    };
    const result = new XmlImportProcessor().processValueMap(valueMap, metadataList);
    expect(result.acceptedValues).toEqual({
      1: ['abc', '123', 'true'],
      2: [123],
      3: ['abc', '123', 'true'],
      4: [true],
    });
    expect(result.rejectedValues).toEqual({
      2: ['abc', 'true'],
      4: ['abc', '123'],
    });
  });

  it('rejects values without matching metadata', () => {
    const metadataList = [
      metadata(1, 'one', 'text'),
    ];
    const valueMap = {
      1: ['abc', '123', 'true'],
      2: ['abc', '123', 'true'],
      3: ['abc', '123', 'true'],
    };
    const result = new XmlImportProcessor().processValueMap(valueMap, metadataList);
    expect(result.acceptedValues).toBeDefined();
    expect(result.extraValues).toEqual({
      2: ['abc', '123', 'true'],
      3: ['abc', '123', 'true'],
    });
  });

  it('matches metadata by ids and names', () => {
    const metadataList = [
      metadata(1, 'one', 'text'),
      metadata(2, 'two', 'text'),
    ];
    const valueMap = {
      1: ['abc', 'asd'],
      'two': ['abc', 'asd'],
    };
    const result = new XmlImportProcessor().processValueMap(valueMap, metadataList);
    expect(result.acceptedValues).toEqual({
      1: ['abc', 'asd'],
      2: ['abc', 'asd'],
    });
  });

  it('recognizes boolean-ish values', () => {
    const metadataList = [metadata(1, 'one', 'boolean')];
    const valueMap = {1: ['true', 'false', '1', '0', '']};
    const result = new XmlImportProcessor().processValueMap(valueMap, metadataList);
    expect(result.acceptedValues).toEqual({1: [true, false, true, false, false]});
    expect(result.rejectedValues).toBeUndefined();
  });
});
