import {Configure} from "aurelia-configuration";
import {RequiredInMainLanguageValidationRule} from "./required-in-main-language";

describe(RequiredInMainLanguageValidationRule.name, () => {
  let config: Configure;
  let rule: RequiredInMainLanguageValidationRule;

  beforeEach(() => {
    config = new Configure();
    spyOn(config, 'get').and.returnValue(['PL', 'EN']);
    rule = new RequiredInMainLanguageValidationRule(config);
  });

  it('contains the main language in the error message', () => {
    expect(rule.message()).toContain('PL');
  });

  it('validates value that contains main language', () => {
    expect(rule.validationFunction()({PL: 'text'})).toBeTruthy();
  });

  it('invalidates value that does not contain main language', () => {
    expect(rule.validationFunction()({EN: 'text'})).toBeFalsy();
  });

  it('invalidates value that contains empty main language', () => {
    expect(rule.validationFunction()({PL: ''})).toBeFalsy();
  });

  it('invalidates value that contains blank main language', () => {
    expect(rule.validationFunction()({PL: '           '})).toBeFalsy();
  });

  it('invalidates value that contains undefined main language', () => {
    expect(rule.validationFunction()({PL: undefined})).toBeFalsy();
  });

  it('validates undefined', () => {
    expect(rule.validationFunction()(undefined)).toBeTruthy();
  });
});
