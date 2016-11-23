import {RequiredInAllLanguagesValidationRule} from "./required-in-all-languages";
import {LanguageRepository, LanguagesChangedEvent} from "../../../resources-config/language-config/language-repository";
import {EventAggregator} from "aurelia-event-aggregator";

describe(RequiredInAllLanguagesValidationRule.name, () => {
  let languageRepository: LanguageRepository;
  let rule: RequiredInAllLanguagesValidationRule;
  let eventAggregator: EventAggregator;

  beforeEach((ready) => {
    languageRepository = new LanguageRepository(undefined, eventAggregator);
    eventAggregator = new EventAggregator();
    spyOn(languageRepository, 'getList').and.returnValue(new Promise(resolve => resolve([{code: 'PL'}, {code: 'EN'}])));
    rule = new RequiredInAllLanguagesValidationRule(languageRepository, eventAggregator);
    languageRepository.getList().then(ready);
  });

  it('validates value that contains both languages', () => {
    expect(rule.validationFunction()({PL: 'text', EN: 'text'})).toBeTruthy();
  });

  it('invalidates value that does not contain one language', () => {
    expect(rule.validationFunction()({EN: 'text'})).toBeFalsy();
  });

  it('invalidates value that contains empty main language', () => {
    expect(rule.validationFunction()({PL: '', EN: 'text'})).toBeFalsy();
  });

  it('invalidates value that contains blank main language', () => {
    expect(rule.validationFunction()({PL: '           ', EN: 'text'})).toBeFalsy();
  });

  it('invalidates value that contains undefined main language', () => {
    expect(rule.validationFunction()({PL: undefined, EN: 'text'})).toBeFalsy();
  });

  it('validates undefined', () => {
    expect(rule.validationFunction()(undefined)).toBeTruthy();
  });

  it("invalides value that contains extra language", () => {
    expect(rule.validationFunction()({PL: 'text', EN: 'text', RUS: 'text'})).toBeFalsy();
  });

  it("updates the list after the languages changed event", (done) => {
    languageRepository.getList = jasmine.createSpy('findAll').and.returnValue(new Promise(resolve =>
      resolve([{code: 'PL'}, {code: 'EN'}, {code: 'RUS'}])));
    eventAggregator.publish(new LanguagesChangedEvent());
    languageRepository.getList().then(() => {
      expect(rule.validationFunction()({PL: 'text', EN: 'text', RUS: 'text'})).toBeTruthy();
      expect(rule.validationFunction()({PL: 'text', EN: 'text'})).toBeFalsy();
      done();
    });
  });
});
