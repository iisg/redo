import {allValues, oneValue} from "./resource-display-strategy-handlebars-helpers";
import {MetadataValue} from "../../../resources/metadata-value";

describe('resource-display-strategy-helpers', () => {
  describe(oneValue.name, () => {
    it('prints one value', () => {
      expect(oneValue(new MetadataValue('ala'))).toEqual('ala');
    });

    it('prints first value from array by default', () => {
      expect(oneValue([new MetadataValue('ala'), new MetadataValue('ola')])).toEqual('ala');
    });

    it('prints specified value from array', () => {
      expect(oneValue([new MetadataValue('ala'), new MetadataValue('ola')], 1)).toEqual('ola');
    });

    it('does not throw error on invalid input', () => {
      expect(oneValue(undefined)).toEqual('');
      expect(oneValue(23)).toEqual('');
      expect(oneValue([])).toEqual('');
      expect(oneValue({})).toEqual('');
    });
  });

  describe(allValues.name, () => {
    it('prints all values', () => {
      expect(allValues([new MetadataValue('ala'), new MetadataValue('ola')])).toEqual('ala, ola');
    });

    it('allows to specify delimiter', () => {
      expect(allValues([new MetadataValue('ala'), new MetadataValue('ola')], '|')).toEqual('ala|ola');
    });

    it('does not throw error on invalid input', () => {
      expect(allValues(undefined)).toEqual('');
      expect(allValues(23)).toEqual('');
      expect(allValues([])).toEqual('');
      expect(allValues({})).toEqual('');
    });
  });
});
