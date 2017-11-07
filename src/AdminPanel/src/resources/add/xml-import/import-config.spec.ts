import {XmlConfigMapping, XmlImportConfig, XmlImportConfigExecutor} from "./import-config";

describe(XmlImportConfigExecutor.name, () => {
  it('augments bare objects with methods', () => {
    const config = new XmlImportConfigExecutor({
      transforms: {
        test: {
          regex: "",
          replacement: ""
        }
      },
      mappings: {
        1: {
          selector: "[tag=1]",
          value: []
        }
      }
    } as any as XmlImportConfig);
    expect(config.transforms['test'].apply).toBeDefined();
    expect(config.mappings[1].getFields).toBeDefined();
  });
});

describe(XmlConfigMapping.name, () => {
  let marc: XMLDocument;

  beforeEach(() => {
    marc = $.parseXML(`
      <record>
        <controlfield tag="001">ctrl</controlfield>
        <datafield tag="123">
          <subfield code="x">Foo</subfield>
        </datafield>
        <datafield tag="1234">
          <subfield code="a">Qwerty</subfield>
          <subfield code="b">Asdfgh</subfield>
          <subfield code="c">Zxcvbn</subfield>
        </datafield>
        <datafield tag="1234">
          <subfield code="z">ZZZ</subfield>
        </datafield>
        <datafield tag="12345">
          <subfield code="m">m1</subfield>
          <subfield code="m">m2</subfield>
          <subfield code="n">n</subfield>
        </datafield>
      </record>
    `);
  });

  it('parses MARC datafields', () => {
    const mapping = new XmlConfigMapping('[tag=1234]', []);
    expect(mapping.getFields(marc)).toEqual([
      {
        a: ['Qwerty'],
        b: ['Asdfgh'],
        c: ['Zxcvbn'],
      }, {
        z: ['ZZZ'],
      },
    ]);
  });

  it('parses MARC datafields with repeated values', () => {
    const mapping = new XmlConfigMapping('[tag=12345]', []);
    expect(mapping.getFields(marc)).toEqual([
      {
        m: ['m1', 'm2'],
        n: ['n'],
      },
    ]);
  });

  it('parses MARC controlfields', () => {
    const mapping = new XmlConfigMapping('[tag=001]', []);
    expect(mapping.getFields(marc)).toEqual([{'*': ['ctrl']}]);
  });

  it('validates field format', () => {
    new XmlConfigMapping('[tag=123]', []);
    new XmlConfigMapping('[ind1= ]', []);
    new XmlConfigMapping('[ind1=1]', []);
    new XmlConfigMapping('[ind2= ]', []);
    new XmlConfigMapping('[ind2=1]', []);
    new XmlConfigMapping('[tag=123][ind1= ]', []);
    new XmlConfigMapping('controlfield', []);
    expect(() => new XmlConfigMapping('[foo=1]', [])).toThrow();
    expect(() => new XmlConfigMapping('[ind1=]', [])).toThrow();
    expect(() => new XmlConfigMapping('[ind3=1]', [])).toThrow();
    expect(() => new XmlConfigMapping('[tag=a]', [])).toThrow();
    expect(() => new XmlConfigMapping('[]', [])).toThrow();
    expect(() => new XmlConfigMapping('[=]', [])).toThrow();
    expect(() => new XmlConfigMapping('foo=1', [])).toThrow();
    expect(() => new XmlConfigMapping('[tag=1]]', [])).toThrow();
  });
});
