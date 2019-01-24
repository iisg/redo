import {deepCopy} from '../../common/utils/object-utils';
import {InCurrentLanguageValueConverter} from '../../resources-config/multilingual-field/in-current-language';
import {ExceptionParamsValueConverter} from "./exception-params-value-converter";

describe(ExceptionParamsValueConverter.name, () => {
  let converter: ExceptionParamsValueConverter;
  let inCurrentLanguage: InCurrentLanguageValueConverter;

  beforeEach(() => {
    inCurrentLanguage = new InCurrentLanguageValueConverter(undefined, undefined);
    spyOn(inCurrentLanguage, 'toView').and.callFake(label => label['PL']);
    converter = new ExceptionParamsValueConverter(inCurrentLanguage);
  });

  describe('parametrized tests', () => {
    [
      {
        name: 'converts empty object',
        params: {},
        expected: {}
      },
      {
        name: 'leaves top-level scalar values',
        params: {
          stringy: "abcd",
          numbery: 1234,
          123: 12345
        },
        expected: {
          stringy: "abcd",
          numbery: 1234,
          123: 12345
        },
      },
      {
        name: 'joins arrays',
        params: {
          empty: [],
          array: ["123", "456"],
          nested: [["123", "456"], ["abc", "def"], [[]]]
        },
        expected: {
          empty: '',
          array: '123, 456',
          nested: '123, 456, abc, def, '
        }
      },
      {
        name: 'stringifies object',
        params: {
          object: {abc: 'def'},
          nestedObject: {abc: {def: 'ghi'}}
        },
        expected: {
          object: '{"abc":"def"}',
          nestedObject: '{"abc":"{\\\"def\\\":\\\"ghi\\\"}"}'
        }
      },
      {
        name: 'translates label and applies backticks',
        params: {
          sth: {label: {PL: 'polish', EN: 'english'}}
        },
        expected: {
          sth: '`polish`'
        }
      },
      {
        name: 'translates labels deep in structure',
        params: {
          array: [
            {label: {PL: 'pl_1', EN: 'en_1'}},
            {label: {PL: 'pl_2', EN: 'en_2'}},
          ],
          object: {
            abc: {label: {PL: 'pl_1', EN: 'en_1'}},
            def: {label: {PL: 'pl_2', EN: 'en_2'}},
          }
        },
        expected: {
          array: '`pl_1`, `pl_2`',
          object: '{"abc":"`pl_1`","def":"`pl_2`"}'
        }
      },
      {
        name: 'does not translate top-level label',
        params: {
          label: {PL: 'pl', EN: 'en'}
        },
        expected: {
          label: '{"PL":"pl","EN":"en"}'
        }
      }
    ].forEach(testCase => {
      it(testCase.name, () => {
        expect(converter.toView(testCase.params)).toEqual(testCase.expected);
      });
    });
  });

  it('does not change given object', () => {
    const params = {
      array: ['abc', {label: {PL: 'pl_1', EN: 'en_1'}}],
      object: {abc: 'def', ghi: {label: {PL: 'pl_1', EN: 'en_1'}}}
    };
    const paramsCopy = deepCopy(params);
    converter.toView(params);
    expect(params).toEqual(paramsCopy);
  });
});
