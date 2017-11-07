import {ImportConfigEvaluator} from "./import-config-evaluator";
import {XmlConfigTransform, ElementContents} from "./import-config";
import createSpyObj = jasmine.createSpyObj;
import Spy = jasmine.Spy;

describe(ImportConfigEvaluator.name, () => {
  let transforms: StringMap<XmlConfigTransform>;
  let evaluator: ImportConfigEvaluator;
  let input: ElementContents[];

  beforeEach(() => {
    const testTransform: XmlConfigTransform = createSpyObj('testTransform', ['apply']) as any;
    (testTransform.apply as Spy).and.returnValue(['transformed']);
    transforms = {'test': testTransform};
    evaluator = new ImportConfigEvaluator(transforms);
    input = [
      {
        a: ["test"],
        b: ["TEST"]
      },
      {
        a: ["test2"],
        b: ["TEST2"]
      }
    ];
  });

  it('evaluates valid config', () => {
    const result = evaluator.evaluate(input, ["a", "' ! '", "b"]);
    expect(result).toEqual(['test ! TEST', 'test2 ! TEST2']);
  });

  it('evaluates valid config with transforms', () => {
    const result = evaluator.evaluate(input, ["a", "' ! '", "b|test"]);
    expect(result).toEqual(['test ! transformed', 'test2 ! transformed']);
    expect((transforms['test'].apply as Spy).calls.allArgs()).toEqual([[['TEST']], [['TEST2']]]);
  });

  it('evaluates valid config with multiple transforms', () => {
    const result = evaluator.evaluate(input, ["a", "' ! '", "b|test|test"]);
    expect(result).toEqual(['test ! transformed', 'test2 ! transformed']);
    expect((transforms['test'].apply as Spy).calls.allArgs()).toEqual([[['TEST']], [['transformed']], [['TEST2']], [['transformed']]]);
  });

  it('accepts star as subfield name', () => {
    const result = evaluator.evaluate([{'*': ['startest']}], ["*"]);
    expect(result).toEqual(['startest']);
  });

  it('fails on unclosed literals', () => {
    expect(() => evaluator.evaluate(input, ["'unclosed"])).toThrow();
  });

  it('fails on unknown transform', () => {
    expect(() => evaluator.evaluate(input, ["a|invalidTransformName"])).toThrow();
  });

  it('fails on missing subfield', () => {
    expect(() => evaluator.evaluate(input, ["c"])).toThrow();
  });

  it('fails on invalid subfield format', () => {
    expect(() => evaluator.evaluate(input, ["."])).toThrow();
    expect(() => evaluator.evaluate(input, ["!"])).toThrow();
    expect(() => evaluator.evaluate(input, [".a"])).toThrow();
  });

  it('fails on invalid subfield format', () => {
    expect(() => evaluator.evaluate(input, ["a."])).toThrow();
    expect(() => evaluator.evaluate(input, ["a2|test"])).toThrow();
    expect(() => evaluator.evaluate(input, ["a.|test"])).toThrow();
  });
});
